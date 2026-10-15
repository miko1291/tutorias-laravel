<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\AlumnoExpediente;
use App\Models\ExpedientePregunta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ExpedienteController extends Controller
{
    public function formulario()
    {
        ExpedientePreguntaController::ensurePreguntasBase();
        $alumno = $this->findAlumnoActual();
        $expediente = AlumnoExpediente::where('id_alumno', $alumno->id)->orderByDesc('id')->first();

        return view('expediente.formulario', [
            'alumno' => $alumno,
            'expediente' => $expediente,
            'respuestas' => array_replace([
                'campo_002' => now()->toDateString(),
                'campo_004' => $alumno->correo,
                'campo_009' => $alumno->telefono,
                'fecha_nacimiento' => $alumno->fecha_nac,
            ], $expediente ? $expediente->getRespuestas() : []),
            'preguntasPersonalizadas' => ExpedientePregunta::activas(),
        ]);
    }

    public function ficha()
    {
        $alumno = $this->findAlumnoActual();
        $expediente = AlumnoExpediente::where('id_alumno', $alumno->id)->orderByDesc('id')->first();

        return view('expediente.ficha', [
            'alumno' => $alumno,
            'expediente' => $expediente,
            'respuestas' => $expediente ? $expediente->getRespuestas() : [],
            'allowEdit' => true,
        ]);
    }

    public function perfil()
    {
        $alumno = $this->findAlumnoActual();
        $expediente = AlumnoExpediente::where('id_alumno', $alumno->id)->orderByDesc('id')->first();

        return view('expediente.perfil', [
            'alumno' => $alumno,
            'expediente' => $expediente,
        ]);
    }

    public function guardar(Request $request)
    {
        $alumno = $this->findAlumnoActual();
        $this->guardarExpediente($request, $alumno);

        return redirect('/expediente/ficha')
            ->with('success', 'La actualización del expediente fue guardada correctamente.');
    }

    private function guardarExpediente(Request $request, Alumno $alumno): void
    {
        ExpedientePreguntaController::ensurePreguntasBase();
        $submitted = $request->input('respuestas', []);
        abort_unless(is_array($submitted), 400, 'Formato de respuestas inválido.');
        $questions = ExpedientePregunta::activas()->keyBy('clave');
        $rules = ['respuestas' => ['present', 'array']];
        $attributes = [];
        foreach ($questions as $question) {
            $key = 'respuestas.'.$question->clave;
            $visible = $question->visibleCon($submitted, $questions);
            $attributes[$key] = $question->titulo;
            if (! $visible) {
                continue;
            }
            $settings = $question->configuracion ?? [];
            $fieldRules = [$question->requerido ? 'required' : 'nullable'];
            if ($question->tipo === 'checkbox') {
                $fieldRules[] = 'array';
                if ($question->requerido) {
                    $fieldRules[] = 'min:1';
                }
                $rules[$key.'.*'] = ['string', Rule::in($question->opciones)];
            } elseif ($question->tipo === 'number') {
                $fieldRules[] = 'numeric';
                foreach (['min', 'max'] as $limit) {
                    if (isset($settings[$limit]) && is_numeric($settings[$limit])) {
                        $fieldRules[] = $limit.':'.$settings[$limit];
                    }
                }
            } elseif ($question->tipo === 'date') {
                $fieldRules[] = 'date_format:Y-m-d';
                foreach (['min' => 'after_or_equal', 'max' => 'before_or_equal'] as $limit => $rule) {
                    if (! empty($settings[$limit])) {
                        $fieldRules[] = $rule.':'.$settings[$limit];
                    }
                }
            } else {
                $fieldRules[] = 'string';
                $fieldRules[] = 'max:'.($settings['maxlength'] ?? ($question->tipo === 'textarea' ? 10000 : 255));
                if ($question->tipo === 'email') {
                    $fieldRules[] = 'email';
                }
                if (in_array($question->tipo, ['select', 'radio'], true)) {
                    $fieldRules[] = Rule::in($question->opciones);
                }
                if (! empty($settings['pattern'])) {
                    $fieldRules[] = 'regex:~^(?:'.str_replace('~', '\\~', $settings['pattern']).')$~u';
                }
            }
            $rules[$key] = $fieldRules;
        }
        $validated = $request->validate($rules, [
            'required' => 'Completa :attribute.',
            'in' => 'Selecciona una opción válida en :attribute.',
            'email' => 'Escribe un correo válido en :attribute.',
            'regex' => 'Revisa el formato de :attribute.',
            'array' => 'Selecciona las opciones de :attribute.',
            'date_format' => 'Escribe una fecha válida en :attribute.',
        ], $attributes);

        DB::transaction(function () use ($validated, $submitted, $questions, $alumno): void {
            $expediente = AlumnoExpediente::where('id_alumno', $alumno->id)->latest('id')->lockForUpdate()->first();
            $answers = $expediente?->getRespuestas() ?? [];
            foreach ($questions as $question) {
                $answers[$question->clave] = $question->visibleCon($submitted, $questions)
                    ? ($validated['respuestas'][$question->clave] ?? ($question->tipo === 'checkbox' ? [] : null))
                    : ($question->tipo === 'checkbox' ? [] : null);
            }
            $birthDate = $answers['fecha_nacimiento'] ?? null;
            if ($birthDate) {
                $date = Carbon::createFromFormat('!Y-m-d', $birthDate);
                $alumno->fecha_nac = $birthDate;
                $alumno->save();
                $answers['campo_007'] = $date->age;
                unset($answers['campo_008']);
            }
            foreach (['enfermedades', 'infecciosas', 'idiomas', 'habitos_estudio', 'barreras', 'disc_fisica_tipo', 'disc_sensorial_tipo', 'disc_intelectual_tipo', 'disc_psiquica_tipo', 'disc_visceral_tipo'] as $key) {
                if (isset($answers[$key]) && $answers[$key] === []) {
                    $answers[$key] = ['Ninguna'];
                }
            }
            if (! $expediente) {
                $expediente = new AlumnoExpediente(['id_alumno' => $alumno->id, 'version' => 1]);
            } else {
                $expediente->version++;
            }
            $expediente->respuestas_json = $answers;
            $expediente->estatus = AlumnoExpediente::ESTATUS_ENVIADO;
            $expediente->save();
        });
    }

    protected function findAlumnoActual(): Alumno
    {
        $alumno = Alumno::where('id_user', Auth::id())->first();

        if (! $alumno) {
            abort(404, 'No existe un alumno asociado con esta cuenta.');
        }

        return $alumno;
    }
}
