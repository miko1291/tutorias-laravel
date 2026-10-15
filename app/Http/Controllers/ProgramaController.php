<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Canalizacion;
use App\Models\Criterio;
use App\Models\Diagnostico;
use App\Models\Evaluacion;
use App\Models\Pat;
use App\Models\Performance;
use App\Models\Semana;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProgramaController extends Controller
{
    public function diagnostico()
    {
        $alumnos = $this->alumnosVisibles();

        return view('programa.index', [
            'titulo' => 'Diagnóstico',
            'columnas' => ['Alumno', 'Motivo', 'Asignaturas', 'Otro', 'Especifique'],
            'filas' => Diagnostico::with('alumno')->whereIn('id_alumno', $alumnos->pluck('id'))->latest('id')->get()->map(fn (Diagnostico $item): array => [$item->alumno->nombre_completo, $item->motivo, $item->asignaturas, $item->otro, $item->especifique]),
            'formulario' => 'diagnostico',
            'alumnos' => $alumnos,
        ]);
    }

    public function guardarDiagnostico(Request $request)
    {
        $data = $request->validate([
            'id_alumno' => ['required', 'integer', Rule::exists('alumno', 'id')],
            'motivo' => ['required', 'integer', Rule::in([1, 2, 3, 4])],
            'asignaturas' => ['required', 'string'],
            'otro' => ['required', 'string'],
            'especifique' => ['required', 'string'],
        ]);
        $this->authorizeAlumno((int) $data['id_alumno']);
        Diagnostico::create($data);

        return back()->with('success', 'Diagnóstico guardado correctamente.');
    }

    public function eliminarDiagnostico(int $id)
    {
        $item = Diagnostico::findOrFail($id);
        $this->authorizeAlumno((int) $item->id_alumno);
        $item->delete();

        return back()->with('success', 'Diagnóstico eliminado correctamente.');
    }

    public function editarDiagnostico(int $id)
    {
        $item = Diagnostico::findOrFail($id);
        $this->authorizeAlumno((int) $item->id_alumno);

        return $this->formularioPrograma('diagnostico', $item);
    }

    public function actualizarDiagnostico(Request $request, int $id)
    {
        $item = Diagnostico::findOrFail($id);
        $this->authorizeAlumno((int) $item->id_alumno);
        $item->update($request->validate([
            'motivo' => ['required', 'integer', Rule::in([1, 2, 3, 4])],
            'asignaturas' => ['required', 'string'],
            'otro' => ['required', 'string'],
            'especifique' => ['required', 'string'],
        ]));

        return redirect('/diagnostico/index')->with('success', 'Diagnóstico actualizado correctamente.');
    }

    public function evaluacion()
    {
        $alumnos = $this->alumnosVisibles();

        return view('programa.index', [
            'titulo' => 'Evaluación',
            'columnas' => ['Alumno', 'Criterio', 'Calificación'],
            'filas' => Evaluacion::with(['alumno', 'criterio'])->whereIn('id_alumno', $alumnos->pluck('id'))->latest('id')->get()->map(fn (Evaluacion $item): array => [$item->alumno->nombre_completo, $item->criterio->nombre, $item->calificacion]),
            'formulario' => 'evaluacion',
            'alumnos' => $alumnos,
            'criterios' => Criterio::orderBy('id')->get(),
        ]);
    }

    public function guardarEvaluacion(Request $request)
    {
        $data = $request->validate([
            'id_alumno' => ['required', 'integer', Rule::exists('alumno', 'id')],
            'id_criterio' => ['required', 'integer', Rule::exists('criterios', 'id')],
            'calificacion' => ['required', 'integer', 'min:0', 'max:100'],
        ]);
        $this->authorizeAlumno((int) $data['id_alumno']);
        Evaluacion::create($data);

        return back()->with('success', 'Evaluación guardada correctamente.');
    }

    public function eliminarEvaluacion(int $id)
    {
        $item = Evaluacion::findOrFail($id);
        $this->authorizeAlumno((int) $item->id_alumno);
        $item->delete();

        return back()->with('success', 'Evaluación eliminada correctamente.');
    }

    public function editarEvaluacion(int $id)
    {
        $item = Evaluacion::findOrFail($id);
        $this->authorizeAlumno((int) $item->id_alumno);

        return $this->formularioPrograma('evaluacion', $item);
    }

    public function actualizarEvaluacion(Request $request, int $id)
    {
        $item = Evaluacion::findOrFail($id);
        $this->authorizeAlumno((int) $item->id_alumno);
        $item->update($request->validate([
            'id_criterio' => ['required', 'integer', Rule::exists('criterios', 'id')],
            'calificacion' => ['required', 'integer', 'min:0', 'max:100'],
        ]));

        return redirect('/evaluacion/index')->with('success', 'Evaluación actualizada correctamente.');
    }

    public function canalizacion()
    {
        $alumnos = $this->alumnosVisibles();

        return view('programa.index', [
            'titulo' => 'Canalización',
            'columnas' => ['Alumno', 'Estado', 'Asunto', 'Cuerpo'],
            'filas' => Canalizacion::with('alumno')->whereIn('id_alumno', $alumnos->pluck('id'))->latest('id')->get()->map(fn (Canalizacion $item): array => [$item->alumno->nombre_completo, $item->estatus ? 'Canalizado' : 'No canalizado', $item->asunto, $item->cuerpo]),
            'formulario' => 'canalizacion',
            'alumnos' => $alumnos,
        ]);
    }

    public function guardarCanalizacion(Request $request)
    {
        $data = $request->validate([
            'id_alumno' => ['required', 'integer', Rule::exists('alumno', 'id')],
            'estatus' => ['required', 'integer', Rule::in([0, 1])],
            'asunto' => ['required', 'string'],
            'cuerpo' => ['required', 'string'],
        ]);
        $this->authorizeAlumno((int) $data['id_alumno']);
        Canalizacion::create($data);

        return back()->with('success', 'Canalización guardada correctamente.');
    }

    public function eliminarCanalizacion(int $id)
    {
        $item = Canalizacion::findOrFail($id);
        $this->authorizeAlumno((int) $item->id_alumno);
        $item->delete();

        return back()->with('success', 'Canalización eliminada correctamente.');
    }

    public function editarCanalizacion(int $id)
    {
        $item = Canalizacion::findOrFail($id);
        $this->authorizeAlumno((int) $item->id_alumno);

        return $this->formularioPrograma('canalizacion', $item);
    }

    public function actualizarCanalizacion(Request $request, int $id)
    {
        $item = Canalizacion::findOrFail($id);
        $this->authorizeAlumno((int) $item->id_alumno);
        $item->update($request->validate([
            'estatus' => ['required', 'integer', Rule::in([0, 1])],
            'asunto' => ['required', 'string'],
            'cuerpo' => ['required', 'string'],
        ]));

        return redirect('/canalizacion/index')->with('success', 'Canalización actualizada correctamente.');
    }

    public function pat()
    {
        return view('programa.index', [
            'titulo' => 'Programa de Acción Tutorial',
            'columnas' => ['Nombre', 'Semestre', 'Descripción', 'Estado'],
            'filas' => Pat::with('semestre')->orderBy('id')->get()->map(fn (Pat $item): array => [$item->nombre, $item->semestre->nombre, $item->descripcion, $item->estatus ? 'Activo' : 'Inactivo']),
            'formulario' => null,
        ]);
    }

    public function semanas()
    {
        return view('programa.index', [
            'titulo' => 'Semanas del programa',
            'columnas' => ['Semana', 'Nombre', 'PAT', 'Temática'],
            'filas' => Semana::with('pat')->orderBy('num_semana')->get()->map(fn (Semana $item): array => [$item->num_semana, $item->nombre, $item->pat->nombre, $item->tematica]),
            'formulario' => null,
        ]);
    }

    public function performance()
    {
        return view('programa.index', [
            'titulo' => 'Desempeño de grupos',
            'columnas' => ['Grupo', 'Excelente', 'Bueno', 'Alto riesgo', 'Comentarios'],
            'filas' => Performance::with('grupo')->latest('id')->get()->map(fn (Performance $item): array => [$item->grupo->nombre, $item->eDesempeño, $item->bDesempeño, $item->arDesempeño, $item->comentarios]),
            'formulario' => null,
        ]);
    }

    private function alumnosVisibles()
    {
        if (Auth::user()->hasAnyRole(['admin', 'psicologo'])) {
            return Alumno::orderBy('apellidop')->orderBy('nombres')->get();
        }

        return Alumno::where('id_user', Auth::id())->orWhereIn('id_grupo', $this->gruposDelTutor())->orderBy('apellidop')->get();
    }

    private function gruposDelTutor()
    {
        return Auth::user()->tutor?->grupoMasters()->pluck('id') ?? collect();
    }

    private function authorizeAlumno(int $id): void
    {
        abort_unless($this->alumnosVisibles()->contains('id', $id), 403, 'No tienes permiso para este alumno.');
    }

    private function formularioPrograma(string $tipo, object $item)
    {
        $alumnos = $this->alumnosVisibles();

        return view('programa.edit', [
            'tipo' => $tipo,
            'item' => $item,
            'alumnos' => $alumnos,
            'criterios' => Criterio::orderBy('id')->get(),
        ]);
    }
}
