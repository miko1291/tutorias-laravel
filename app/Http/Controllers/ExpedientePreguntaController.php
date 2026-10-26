<?php

namespace App\Http\Controllers;

use App\Models\ExpedientePregunta;
use App\Services\ExpedienteCatalogoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ExpedientePreguntaController extends Controller
{
    public function index(Request $request)
    {
        self::ensurePreguntasBase();

        return view('expediente-pregunta.index', ['designerData' => $this->designerData()]);
    }

    private function designerData(): array
    {
        $all = ExpedientePregunta::withTrashed()->orderBy('id')->get();
        $serialize = static fn (ExpedientePregunta $question): array => [
            'id' => $question->id, 'clave' => $question->clave, 'titulo' => $question->titulo,
            'descripcion' => $question->descripcion, 'tipo' => $question->tipo,
            'seccion' => $question->seccion, 'seccion_orden' => $question->seccion_orden,
            'orden' => $question->orden, 'opciones' => $question->opciones,
            'requerido' => (bool) $question->requerido, 'activo' => (bool) $question->activo,
            'configuracion' => $question->configuracion ?? [],
        ];

        return [
            'revision' => hash('sha256', $all->toJson()),
            'preguntas' => $all->reject->trashed()->sortBy([
                ['seccion_orden', 'asc'], ['seccion', 'asc'], ['orden', 'asc'], ['id', 'asc'],
            ])->map($serialize)->values()->all(),
            'archivadas' => $all->filter->trashed()->map($serialize)->values()->all(),
        ];
    }

    public function guardarDiseno(Request $request): JsonResponse
    {
        $data = $request->validate([
            'revision' => ['required', 'string', 'size:64'],
            'secciones' => ['required', 'array', 'min:1', 'max:50'],
            'secciones.*.titulo' => ['required', 'string', 'max:120', 'distinct'],
            'secciones.*.preguntas' => ['present', 'array', 'min:1', 'max:300'],
            'secciones.*.preguntas.*.id' => ['nullable', 'integer', 'distinct', 'exists:expediente_pregunta,id'],
            'secciones.*.preguntas.*.clave' => ['required', 'string', 'regex:/^[a-z][a-z0-9_]{2,80}$/', 'distinct'],
            'secciones.*.preguntas.*.titulo' => ['required', 'string', 'max:255'],
            'secciones.*.preguntas.*.descripcion' => ['nullable', 'string', 'max:2000'],
            'secciones.*.preguntas.*.tipo' => ['required', Rule::in(['text', 'textarea', 'email', 'tel', 'number', 'date', 'select', 'radio', 'checkbox'])],
            'secciones.*.preguntas.*.opciones' => ['present', 'array', 'max:5000'],
            'secciones.*.preguntas.*.opciones.*' => ['required', 'string', 'max:255'],
            'secciones.*.preguntas.*.requerido' => ['required', 'boolean'],
            'secciones.*.preguntas.*.activo' => ['required', 'boolean'],
            'secciones.*.preguntas.*.configuracion' => ['present', 'array:placeholder,min,max,maxlength,pattern,etiquetas,condicion,municipios_por_estado,estado_clave'],
            'secciones.*.preguntas.*.configuracion.municipios_por_estado' => ['sometimes', 'array', 'max:100'],
            'secciones.*.preguntas.*.configuracion.municipios_por_estado.*' => ['array', 'max:500'],
            'secciones.*.preguntas.*.configuracion.municipios_por_estado.*.*' => ['string', 'max:255'],
            'secciones.*.preguntas.*.configuracion.estado_clave' => ['sometimes', 'string', 'max:90'],
            'secciones.*.preguntas.*.configuracion.placeholder' => ['nullable', 'string', 'max:255'],
            'secciones.*.preguntas.*.configuracion.min' => ['nullable', 'string', 'max:30'],
            'secciones.*.preguntas.*.configuracion.max' => ['nullable', 'string', 'max:30'],
            'secciones.*.preguntas.*.configuracion.maxlength' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'secciones.*.preguntas.*.configuracion.pattern' => ['nullable', 'string', 'max:200'],
            'secciones.*.preguntas.*.configuracion.etiquetas' => ['sometimes', 'array'],
            'secciones.*.preguntas.*.configuracion.etiquetas.*' => ['string', 'max:255'],
            'secciones.*.preguntas.*.configuracion.condicion' => ['nullable', 'array:clave,valor,alternativa'],
            'secciones.*.preguntas.*.configuracion.condicion.clave' => ['required_with:secciones.*.preguntas.*.configuracion.condicion', 'string', 'max:90'],
            'secciones.*.preguntas.*.configuracion.condicion.valor' => ['required_with:secciones.*.preguntas.*.configuracion.condicion', 'string', 'max:255'],
            'secciones.*.preguntas.*.configuracion.condicion.alternativa' => ['nullable', 'string', 'max:90'],
            'eliminadas' => ['present', 'array', 'max:300'],
            'eliminadas.*' => ['integer', 'distinct', 'exists:expediente_pregunta,id'],
        ]);

        DB::transaction(function () use ($data): void {
            $existing = ExpedientePregunta::withTrashed()->orderBy('id')->lockForUpdate()->get();
            abort_unless(hash_equals($data['revision'], hash('sha256', $existing->toJson())), 409, 'El diseño cambió en otra ventana. Recarga antes de guardar.');
            $byId = $existing->keyBy('id');
            $keys = [];
            $definitions = collect($data['secciones'])->flatMap(fn (array $section): array => $section['preguntas']);
            if ($definitions->count() > 300) {
                throw ValidationException::withMessages(['secciones' => 'El expediente admite hasta 300 preguntas.']);
            }
            foreach ($definitions as $definition) {
                $key = $definition['clave'];
                $record = isset($definition['id']) ? $byId->get($definition['id']) : null;
                if (($record && $record->clave !== $key) || (! $record && $existing->contains('clave', $key))) {
                    throw ValidationException::withMessages(['secciones' => 'La identificación de una pregunta existente no puede cambiar.']);
                }
                if ($record && in_array($record->id, $data['eliminadas'], true)) {
                    throw ValidationException::withMessages(['eliminadas' => 'Una pregunta no puede guardarse y archivarse al mismo tiempo.']);
                }
                $keys[$key] = $definition;
                if (in_array($definition['tipo'], ['select', 'radio', 'checkbox'], true) && count($definition['opciones']) === 0) {
                    throw ValidationException::withMessages(['secciones' => 'Agrega opciones a «'.$definition['titulo'].'».']);
                }
            }
            $checkConditions = function (string $key, array $seen = []) use (&$checkConditions, $keys): void {
                if (! isset($keys[$key]) || in_array($key, $seen, true)) {
                    throw ValidationException::withMessages(['secciones' => 'Las condiciones deben depender de preguntas válidas y no formar ciclos.']);
                }
                $condition = $keys[$key]['configuracion']['condicion'] ?? null;
                if ($condition) {
                    $seen[] = $key;
                    foreach (array_filter([$condition['clave'], $condition['alternativa'] ?? null]) as $parent) {
                        $checkConditions($parent, $seen);
                    }
                }
            };
            foreach (array_keys($keys) as $key) {
                $checkConditions($key);
                $pattern = $keys[$key]['configuracion']['pattern'] ?? null;
                if ($pattern && @preg_match('~^(?:'.str_replace('~', '\\~', $pattern).')$~u', '') === false) {
                    throw ValidationException::withMessages(['secciones' => 'Revisa el formato de respuesta de «'.$keys[$key]['titulo'].'».']);
                }
            }
            foreach ($data['secciones'] as $sectionIndex => $section) {
                foreach ($section['preguntas'] as $questionIndex => $definition) {
                    $question = isset($definition['id']) ? $byId->get($definition['id']) : new ExpedientePregunta;
                    if (isset($question->configuracion['municipios_por_estado'])) {
                        $definition['configuracion']['municipios_por_estado'] = $question->configuracion['municipios_por_estado'];
                        $definition['configuracion']['estado_clave'] = $question->configuracion['estado_clave'];
                        $definition['opciones'] = $question->opciones;
                        $definition['tipo'] = 'select';
                    }
                    $question->fill(collect($definition)->except(['id', 'opciones'])->all());
                    $question->seccion = trim($section['titulo']);
                    $question->seccion_orden = $sectionIndex + 1;
                    $question->orden = $questionIndex + 1;
                    $question->opciones_json = json_encode(array_values(array_unique($definition['opciones'])), JSON_UNESCAPED_UNICODE);
                    $question->deleted_at = null;
                    $question->save();
                }
            }
            ExpedientePregunta::whereIn('id', $data['eliminadas'])->delete();
        });

        return response()->json($this->designerData());
    }

    public function guardar(Request $request, ?int $id = null): RedirectResponse
    {
        $pregunta = $id ? ExpedientePregunta::findOrFail($id) : new ExpedientePregunta;
        $data = $request->validate([
            'clave' => [
                'nullable',
                'string',
                'max:90',
                'regex:/^[a-z][a-z0-9_]{2,80}$/',
                Rule::unique('expediente_pregunta', 'clave')->ignore($pregunta->id),
            ],
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'tipo' => ['required', Rule::in(['text', 'textarea', 'number', 'date', 'select', 'radio', 'checkbox'])],
            'seccion' => ['required', 'string', 'max:120'],
            'requerido' => ['sometimes', 'boolean'],
            'activo' => ['sometimes', 'boolean'],
            'orden' => ['required', 'integer', 'min:0'],
            'opciones' => ['nullable', 'string'],
        ]);

        $opciones = preg_split('/\r\n|\r|\n/', (string) ($data['opciones'] ?? ''));
        $opciones = array_values(array_filter(array_map('trim', $opciones), fn (string $opcion): bool => $opcion !== ''));

        $data['clave'] = $data['clave'] ?? ($pregunta->clave ?: 'pregunta_'.Str::lower(Str::random(16)));

        $pregunta->fill($data);
        $pregunta->requerido = $request->boolean('requerido');
        $pregunta->activo = $request->boolean('activo');
        $pregunta->opciones_json = json_encode($opciones, JSON_UNESCAPED_UNICODE);
        $pregunta->save();

        return redirect('/expediente-pregunta/index')->with('success', 'Pregunta guardada correctamente.');
    }

    public function eliminar(int $id)
    {
        ExpedientePregunta::findOrFail($id)->delete();

        return redirect('/expediente-pregunta/index')->with('success', 'Pregunta eliminada.');
    }

    public function toggle(int $id)
    {
        $pregunta = ExpedientePregunta::findOrFail($id);
        $pregunta->update(['activo' => ! $pregunta->activo]);

        return redirect('/expediente-pregunta/index');
    }

    public function ordenar(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'preguntas' => ['required', 'array', 'min:1'],
            'preguntas.*.id' => ['required', 'integer', 'distinct', 'exists:expediente_pregunta,id'],
            'preguntas.*.orden' => ['required', 'integer', 'min:1'],
            'preguntas.*.seccion' => ['required', 'string', 'max:120'],
        ]);

        DB::transaction(function () use ($data): void {
            foreach ($data['preguntas'] as $item) {
                ExpedientePregunta::whereKey($item['id'])->update([
                    'orden' => $item['orden'],
                    'seccion' => trim($item['seccion']),
                ]);
            }
        });

        if ($request->expectsJson()) {
            return response()->json(['message' => 'La estructura del expediente se guardó correctamente.']);
        }

        return redirect('/expediente-pregunta/index')->with('success', 'La estructura del expediente se guardó correctamente.');
    }

    public static function ensurePreguntasBase(): void
    {
        $base = json_decode(file_get_contents(resource_path('expediente-base.json')), true, flags: JSON_THROW_ON_ERROR);
        $existing = ExpedientePregunta::withTrashed()->get()->keyBy('clave');

        foreach ($base as $definition) {
            $question = $existing->get($definition['clave']);
            if (! $question) {
                $definition['opciones_json'] = json_encode($definition['opciones'], JSON_UNESCAPED_UNICODE);
                unset($definition['opciones']);
                ExpedientePregunta::create($definition);
            } elseif ($question->configuracion === null) {
                $question->configuracion = $definition['configuracion'];
                $question->seccion_orden = $definition['seccion_orden'];
                $question->orden = $definition['orden'];
                if ($question->opciones === []) {
                    $question->opciones_json = json_encode($definition['opciones'], JSON_UNESCAPED_UNICODE);
                }
                if ($question->tipo === 'text' && in_array($definition['tipo'], ['email', 'tel'], true)) {
                    $question->tipo = $definition['tipo'];
                }
                $question->save();
            }
        }

        ExpedienteCatalogoService::prepararMunicipios();
    }
}
