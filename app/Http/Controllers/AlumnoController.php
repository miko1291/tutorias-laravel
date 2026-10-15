<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\AlumnoExpediente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

class AlumnoController extends Controller
{
    public function index(Request $request)
    {
        $query = Alumno::query();

        foreach (['id', 'genero', 'id_grupo'] as $campo) {
            if ($request->filled($campo)) {
                $query->where($campo, (int) $request->query($campo));
            }
        }

        if ($request->filled('fecha_nac')) {
            $query->where('fecha_nac', $request->query('fecha_nac'));
        }

        foreach (['nombres', 'apellidop', 'apellidom', 'matricula', 'correo', 'telefono', 'ciudad'] as $campo) {
            if ($request->filled($campo)) {
                $query->where($campo, 'like', '%'.trim((string) $request->query($campo)).'%');
            }
        }

        if ($request->filled('id_grupo')) {
            $query->orderBy('apellidop')->orderBy('apellidom')->orderBy('nombres');
        }

        $alumnos = $query->paginate(100)->withQueryString();

        return view('alumno.index', ['alumnos' => $alumnos]);
    }

    public function view(int $id)
    {
        return view('alumno.view', ['model' => Alumno::findOrFail($id)]);
    }

    public function ficha(int $id)
    {
        $alumno = Alumno::findOrFail($id);
        $grupoSeleccionado = (int) Session::get('id_grupo');

        if ($grupoSeleccionado !== (int) $alumno->id_grupo) {
            abort(403, 'No tienes permiso para consultar este expediente.');
        }

        $expediente = AlumnoExpediente::where('id_alumno', $alumno->id)->orderByDesc('id')->first();

        return view('alumno.ficha', [
            'alumno' => $alumno,
            'expediente' => $expediente,
            'respuestas' => $expediente ? $expediente->getRespuestas() : [],
            'allowEdit' => false,
        ]);
    }

    public function create(Request $request, int $id_grupo = 0)
    {
        $model = new Alumno;
        $model->id_grupo = $id_grupo;

        if ($request->isMethod('post')) {
            $model->fill($request->validate(self::reglas()));
            $model->id_grupo = $id_grupo;
            $model->save();

            return redirect('/grupo-master/index?id='.$model->id.'&id_grupo='.$id_grupo);
        }

        return view('alumno.create', ['model' => $model, 'id_grupo' => $id_grupo]);
    }

    public function update(Request $request, int $id)
    {
        $model = Alumno::findOrFail($id);

        if ($request->isMethod('post')) {
            $model->fill($request->validate(self::reglas()));
            $model->save();

            return redirect('/grupo-master/index?id='.$model->id);
        }

        return view('alumno.update', ['model' => $model]);
    }

    public function delete(int $id)
    {
        Alumno::findOrFail($id)->delete();

        return redirect('/grupo-master/index');
    }

    protected static function reglas(): array
    {
        return [
            'nombres' => ['required', 'string', 'max:100'],
            'apellidop' => ['required', 'string', 'max:50'],
            'apellidom' => ['required', 'string', 'max:50'],
            'matricula' => ['required', 'string', 'max:50'],
            'correo' => ['required', 'string', 'max:50'],
            'telefono' => ['required', 'string', 'max:20'],
            'fecha_nac' => ['required', 'date'],
            'ciudad' => ['required', 'string', 'max:50'],
            'genero' => ['required', 'integer', Rule::in([0, 1])],
            'id_grupo' => ['nullable', 'integer', Rule::exists('grupo_master', 'id')],
        ];
    }
}
