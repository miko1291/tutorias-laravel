<?php

namespace App\Http\Controllers;

use App\Models\ExpedientePregunta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ExpedienteCatalogoController extends Controller
{
    public function index(Request $request): View
    {
        ExpedientePreguntaController::ensurePreguntasBase();
        $listas = ExpedientePregunta::whereIn('tipo', ['select', 'radio', 'checkbox'])
            ->orderBy('seccion_orden')->orderBy('orden')->get();
        $clave = $request->query('lista', 'campo_010');
        $pregunta = $listas->firstWhere('clave', $clave);
        abort_unless($pregunta, 404);
        $estados = ExpedientePregunta::where('clave', 'campo_010')->firstOrFail();
        $esMunicipio = isset($pregunta->configuracion['municipios_por_estado']);
        $estado = $esMunicipio ? $request->query('estado', $estados->opciones[0] ?? 'Otro') : null;
        abort_if($esMunicipio && ! in_array($estado, $estados->opciones, true), 404);
        $opciones = $esMunicipio
            ? ($pregunta->configuracion['municipios_por_estado'][$estado] ?? [])
            : $pregunta->opciones;

        return view('admin.expediente-catalogos', [
            'listas' => $listas, 'pregunta' => $pregunta, 'estados' => $estados,
            'estado' => $estado, 'esMunicipio' => $esMunicipio, 'opciones' => $opciones,
            'revision' => hash('sha256', $pregunta->toJson()),
        ]);
    }

    public function save(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'revision' => ['required', 'string', 'size:64'],
            'estado' => ['nullable', 'string', 'max:255'],
            'etiquetas' => ['sometimes', 'array', 'max:500'],
            'etiquetas.*' => ['required', 'string', 'max:255'],
            'quitar' => ['sometimes', 'array'],
            'quitar.*' => ['integer', 'min:0', 'distinct'],
            'nuevas' => ['nullable', 'string', 'max:50000'],
        ]);
        $pregunta = DB::transaction(function () use ($id, $data): ExpedientePregunta {
            $pregunta = ExpedientePregunta::whereIn('tipo', ['select', 'radio', 'checkbox'])->lockForUpdate()->findOrFail($id);
            abort_unless(hash_equals(hash('sha256', $pregunta->toJson()), $data['revision']), 409, 'Esta lista cambió en otra ventana. Recarga antes de guardar.');
            $configuracion = $pregunta->configuracion ?? [];
            $esMunicipio = isset($configuracion['municipios_por_estado']);
            if ($esMunicipio) {
                $estados = ExpedientePregunta::where('clave', 'campo_010')->firstOrFail();
                validator($data, ['estado' => ['required', Rule::in($estados->opciones)]])->validate();
            }
            $opciones = $esMunicipio ? ($configuracion['municipios_por_estado'][$data['estado']] ?? []) : $pregunta->opciones;
            $etiquetas = array_values($data['etiquetas'] ?? []);
            $quitar = array_map('intval', $data['quitar'] ?? []);
            if (count($etiquetas) !== count($opciones) || array_diff($quitar, array_keys($opciones)) !== []) {
                throw ValidationException::withMessages(['etiquetas' => 'La lista está incompleta. Recarga la página y vuelve a intentarlo.']);
            }
            $resultado = [];
            foreach ($opciones as $index => $valor) {
                if (in_array($index, $quitar, true)) {
                    $this->validateRemoval($pregunta, $valor);

                    continue;
                }
                $configuracion['etiquetas'][$valor] = trim($etiquetas[$index]);
                $resultado[] = $valor;
            }
            $nuevas = array_values(array_filter(array_map('trim', preg_split('/\R/u', $data['nuevas'] ?? '')), fn (string $valor): bool => $valor !== ''));
            foreach ($nuevas as $valor) {
                if (mb_strlen($valor) > 255) {
                    throw ValidationException::withMessages(['nuevas' => 'Cada opción puede tener hasta 255 caracteres.']);
                }
                $resultado[] = $valor;
                $configuracion['etiquetas'][$valor] ??= $valor;
            }
            $nombres = array_map(fn (string $valor): string => mb_strtolower($configuracion['etiquetas'][$valor] ?? $valor), $resultado);
            if (count($resultado) > 500 || count(array_unique($nombres)) !== count($nombres) || in_array('', $nombres, true)) {
                throw ValidationException::withMessages(['nuevas' => 'Usa nombres diferentes y no vacíos. Cada lista admite hasta 500 opciones.']);
            }
            if ($esMunicipio) {
                $configuracion['municipios_por_estado'][$data['estado']] = $resultado;
                $resultado = array_values(array_unique([...array_merge(...array_values($configuracion['municipios_por_estado'])), 'Otro']));
            }
            if ($resultado === []) {
                throw ValidationException::withMessages(['quitar' => 'La lista debe conservar al menos una opción.']);
            }
            $pregunta->update(['configuracion' => $configuracion, 'opciones_json' => json_encode($resultado, JSON_UNESCAPED_UNICODE)]);

            return $pregunta;
        });

        return redirect()->route('admin.expediente-catalogos', ['lista' => $pregunta->clave, 'estado' => $data['estado'] ?? null])
            ->with('success', 'Opciones actualizadas. Los cambios ya están disponibles en el expediente.');
    }

    private function validateRemoval(ExpedientePregunta $pregunta, string $valor): void
    {
        $referenciada = ExpedientePregunta::all()->contains(function (ExpedientePregunta $dependiente) use ($pregunta, $valor): bool {
            $condicion = $dependiente->configuracion['condicion'] ?? [];

            return ($condicion['valor'] ?? null) === $valor
                && in_array($pregunta->clave, [$condicion['clave'] ?? null, $condicion['alternativa'] ?? null], true);
        });
        $municipios = ExpedientePregunta::where('clave', 'campo_012')->first();
        $tieneMunicipios = $pregunta->clave === 'campo_010' && ! empty($municipios?->configuracion['municipios_por_estado'][$valor]);
        if ($referenciada || $tieneMunicipios) {
            throw ValidationException::withMessages(['quitar' => 'No se puede quitar «'.$valor.'» porque tiene municipios o preguntas relacionadas. Puedes editar su nombre.']);
        }
    }
}
