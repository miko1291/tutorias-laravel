<?php

namespace App\Http\Controllers;

use App\Mail\ExpedientePendienteMail;
use App\Mail\NotificationMail;
use App\Models\Alumno;
use App\Models\GrupoMaster;
use App\Models\Notification;
use App\Models\Tutor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class GrupoMasterController extends Controller
{
    public function index(Request $request)
    {
        $idGrupo = $request->session()->get('id_grupo');
        $model = $idGrupo
            ? GrupoMaster::with(['carrera', 'semestre', 'grupoLetra', 'periodo', 'tutor'])->find((int) $idGrupo)
            : null;

        if (! $model) {
            abort(404, 'No hay un grupo seleccionado en la sesión.');
        }

        $filtros = [
            'matricula' => trim((string) $request->query('matricula', '')),
            'apellidop' => trim((string) $request->query('apellidop', '')),
            'apellidom' => trim((string) $request->query('apellidom', '')),
            'nombres' => trim((string) $request->query('nombres', '')),
            'genero' => (string) $request->query('genero', ''),
        ];

        $query = Alumno::query()
            ->where('id_grupo', $model->id)
            ->orderBy('apellidop')
            ->orderBy('apellidom')
            ->orderBy('nombres');

        foreach (['matricula', 'apellidop', 'apellidom', 'nombres'] as $campo) {
            if ($filtros[$campo] !== '') {
                $query->where($campo, 'like', '%'.$filtros[$campo].'%');
            }
        }

        if (in_array($filtros['genero'], ['0', '1'], true)) {
            $query->where('genero', (int) $filtros['genero']);
        }

        $alumnos = $query->paginate(100)->withQueryString();

        return view('grupo-master.index', [
            'model' => $model,
            'alumnos' => $alumnos,
            'filtros' => $filtros,
        ]);
    }

    public function pendientes(Request $request, ?int $idGrupo = null)
    {
        $isAdmin = $request->user()->hasRole('admin');
        $groups = GrupoMaster::with(['carrera', 'semestre', 'grupoLetra', 'tutor']);
        if (! $isAdmin) {
            $groups->whereHas('tutor', fn ($tutor) => $tutor->where('id_user', $request->user()->id));
        }
        $grupos = $groups->orderBy('id')->get();
        $selectedId = $idGrupo ?? $request->integer('id_grupo', $request->integer('idGrupo'));
        if ($selectedId && ! $grupos->contains('id', $selectedId)) {
            abort(403, 'El grupo no está disponible para tu cuenta.');
        }
        $query = Alumno::pendientesExpediente()->with(['latestExpediente', 'grupo.carrera', 'grupo.semestre', 'grupo.grupoLetra', 'grupo.tutor']);
        if (! $isAdmin) {
            $query->whereIn('id_grupo', $grupos->pluck('id'));
        }
        if ($selectedId) {
            $query->where('id_grupo', $selectedId);
        }
        $buscar = trim((string) $request->query('buscar', ''));
        if ($buscar !== '') {
            $query->where(function ($students) use ($buscar): void {
                foreach (['matricula', 'nombres', 'apellidop', 'apellidom'] as $column) {
                    $students->orWhere($column, 'like', '%'.$buscar.'%');
                }
            });
        }
        $pendientes = $query->orderBy('apellidop')->orderBy('apellidom')->orderBy('nombres')->paginate(30)->withQueryString();

        return view('grupo-master.pendientes', [
            'grupos' => $grupos,
            'grupo' => $grupos->firstWhere('id', $selectedId),
            'selectedId' => $selectedId,
            'buscar' => $buscar,
            'isAdmin' => $isAdmin,
            'pendientes' => $pendientes,
            'testEmail' => (string) config('tutorias.expediente_notification_test_email'),
        ]);
    }

    public function enviarPrueba(Request $request)
    {
        $grupo = $this->getTutorGroup($request);
        $pendientes = $this->getPendingStudents($grupo);
        $testEmail = (string) config('tutorias.expediente_notification_test_email');

        if (! $pendientes) {
            return redirect('/grupo-master/pendientes')
                ->with('info', 'No hay alumnos pendientes en el grupo seleccionado.');
        }

        try {
            Mail::to($testEmail)->send(new ExpedientePendienteMail(
                $grupo,
                $pendientes,
                count($pendientes),
                $testEmail,
                true
            ));

            $mensaje = 'Correo de prueba enviado únicamente a '.$testEmail.'. No se envió ningún mensaje a los alumnos.';

            return redirect('/grupo-master/pendientes')->with('success', $mensaje);
        } catch (\Throwable $exception) {
            report($exception);

            return redirect('/grupo-master/pendientes')
                ->with('error', 'No se pudo generar el correo de prueba. Revisa la configuración del correo.');
        }
    }

    public function enviarPendientes(Request $request)
    {
        $grupo = $this->getTutorGroup($request);
        $pendientes = $this->getPendingStudents($grupo);
        if (! $pendientes) {
            return redirect('/grupo-master/pendientes')
                ->with('info', 'No hay alumnos pendientes en el grupo seleccionado.');
        }

        $url = url('/expediente/index');
        $enPortal = 0;
        $enCorreo = 0;
        foreach ($pendientes as $item) {
            $alumno = $item['alumno'];
            $notification = Notification::createForUser(
                $alumno->id_user,
                'NO HAS HECHO TU ACTUALIZACIÓN DE EXPEDIENTE',
                'Tu expediente tutorial está pendiente de completar o actualizar.',
                $url,
                'warning'
            );
            if (! $notification) {
                continue;
            }
            $enPortal++;
            $correo = trim((string) $alumno->correo);
            if ($correo !== '' && filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                try {
                    Mail::to($correo)->send(new NotificationMail($notification->title, $notification->message, $url));
                    $notification->email_sent_at = time();
                    $notification->save();
                    $enCorreo++;
                } catch (\Throwable $exception) {
                    report($exception);
                }
            }
        }

        return redirect('/grupo-master/pendientes')->with(
            'success',
            'Notificaciones creadas en el portal: '.$enPortal.'. Correos enviados: '.$enCorreo.'.'
        );
    }

    private function getTutorGroup(Request $request, ?int $idGrupo = null): GrupoMaster
    {
        if (! Auth::user()->hasRole('tutor')) {
            abort(403, 'Solo los tutores pueden consultar este reporte.');
        }

        $idGrupo = $idGrupo ?? $request->integer('id_grupo', (int) $request->session()->get('id_grupo'));
        $tutor = Tutor::where('id_user', Auth::id())->first();
        $grupo = $tutor
            ? GrupoMaster::where('id', $idGrupo)->where('id_tutor', $tutor->id)->first()
            : null;

        if ($grupo) {
            $request->session()->put('id_grupo', (int) $grupo->id);
        }

        if (! $grupo && $tutor) {
            $gruposAsignados = GrupoMaster::where('id_tutor', $tutor->id)
                ->orderByDesc('id')
                ->get();

            if ($gruposAsignados->count() === 1) {
                $grupo = $gruposAsignados->first();
                $request->session()->put('id_grupo', (int) $grupo->id);
            }
        }

        if (! $grupo) {
            abort(403, 'Selecciona un grupo asignado antes de consultar los pendientes.');
        }

        return $grupo;
    }

    private function getPendingStudents(GrupoMaster $grupo): array
    {
        $pendientes = [];
        foreach ($grupo->alumnos()
            ->with('latestExpediente')
            ->orderBy('apellidop')
            ->orderBy('apellidom')
            ->orderBy('nombres')
            ->get() as $alumno) {
            $expediente = $alumno->latestExpediente;

            if (! $expediente || ! in_array($expediente->estatus, ['enviado', 'aprobado'], true)) {
                $pendientes[] = [
                    'alumno' => $alumno,
                    'expediente' => $expediente,
                    'motivo' => ! $expediente ? 'Sin expediente registrado' : 'Expediente pendiente de completar o actualizar',
                ];
            }
        }

        return $pendientes;
    }
}
