<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\AlumnoExpediente;
use App\Models\GrupoMaster;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function index()
    {
        if (! Auth::check()) {
            return redirect('/site/acceso');
        }

        if (Auth::user()->hasRole('admin')) {
            return redirect('/admin/index');
        }

        $alumno = Alumno::with('grupo')->where('id_user', Auth::id())->first();
        $tutor = Tutor::where('id_user', Auth::id())->first();
        $expediente = $alumno
            ? AlumnoExpediente::where('id_alumno', $alumno->id)->orderByDesc('id')->first()
            : null;

        $grupos = $tutor ? GrupoMaster::with(['carrera', 'semestre', 'grupoLetra'])->withCount('alumnos')->where('id_tutor', $tutor->id)->get() : collect();
        $grupoActual = $grupos->firstWhere('id', (int) session('id_grupo'));
        $pendientesTutor = $tutor ? Alumno::pendientesExpediente()->whereIn('id_grupo', $grupos->pluck('id'))->count() : 0;

        return view('site.index', [
            'alumno' => $alumno,
            'tutor' => $tutor,
            'expediente' => $expediente,
            'grupos' => $grupos,
            'grupoActual' => $grupoActual,
            'pendientesTutor' => $pendientesTutor,
        ]);
    }

    public function acceso()
    {
        return view('site.acceso');
    }

    public function showLogin(Request $request)
    {
        if (Auth::check()) {
            return redirect('/');
        }

        $perfil = $request->query('perfil', 'alumno');
        $perfiles = [
            'alumno' => [
                'nombre' => 'Alumnos',
                'titulo' => 'Acceso para alumnos',
                'descripcion' => 'Consulta y actualiza tu perfil y expediente tutorial.',
                'icono' => 'fa-user-graduate',
            ],
            'tutor' => [
                'nombre' => 'Tutores',
                'titulo' => 'Acceso para tutores',
                'descripcion' => 'Consulta tus grupos y da seguimiento a tus estudiantes.',
                'icono' => 'fa-chalkboard-teacher',
            ],
            'admin' => [
                'nombre' => 'Administradores',
                'titulo' => 'Acceso administrativo',
                'descripcion' => 'Administra usuarios, grupos, permisos y la configuración del programa.',
                'icono' => 'fa-shield-alt',
            ],
            'psicologo' => [
                'nombre' => 'Psicología',
                'titulo' => 'Acceso para psicología',
                'descripcion' => 'Organiza el expediente institucional y consulta las herramientas de seguimiento disponibles para tu cuenta.',
                'icono' => 'fa-heart',
            ],
        ];
        $perfil = is_string($perfil) && array_key_exists($perfil, $perfiles) ? $perfil : 'alumno';
        $perfilActual = $perfiles[$perfil] ?? $perfiles['alumno'];

        return view('site.login', ['perfil' => $perfilActual, 'perfilClave' => $perfil]);
    }

    public function manual(Request $request): View
    {
        $manuales = require resource_path('manuales.php');
        $roles = $request->user()?->roles() ?? [];
        if (in_array('alumno', $roles, true) && ! array_intersect(['admin', 'tutor', 'psicologo', 'psicologa'], $roles)) {
            $manuales = ['alumno' => $manuales['alumno']];
        }
        $default = in_array('admin', $roles, true) ? 'admin' : (in_array('alumno', $roles, true) ? 'alumno' : (array_intersect(['psicologo', 'psicologa'], $roles) ? 'psicologo' : (in_array('tutor', $roles, true) ? 'tutor' : 'alumno')));
        $perfil = $request->query('perfil', $default);
        $perfil = is_string($perfil) && isset($manuales[$perfil]) ? $perfil : $default;

        return view('site.manual', ['manuales' => $manuales, 'perfil' => $perfil, 'manual' => $manuales[$perfil]]);
    }

    public function login(Request $request)
    {
        if (Auth::check()) {
            return redirect('/');
        }

        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ], [
            'username.required' => 'Escribe tu usuario.',
            'password.required' => 'Escribe tu contraseña.',
        ]);

        $user = User::findActiveByUsername($credentials['username']);
        if (! $user || ! Hash::check($credentials['password'], $user->getAuthPassword())) {
            return back()
                ->withErrors(['password' => 'El usuario o la contraseña son incorrectos.'])
                ->withInput($request->only('username'));
        }

        Auth::login($user, $request->boolean('rememberMe', true));
        $request->session()->regenerate();

        // Solo los tutores necesitan seleccionar un grupo al iniciar sesión.
        // Los alumnos no tienen un registro en la tabla tutor.
        if ($user->hasRole('tutor')) {
            $tutor = Tutor::where('id_user', $user->id)->first();
            if (! $tutor) {
                $request->session()->forget('id_grupo');
            } else {
                $gruposCount = GrupoMaster::where('id_tutor', $tutor->id)->count();
                if ($gruposCount === 0) {
                    $request->session()->forget('id_grupo');
                } else {
                    $idGrupo = $gruposCount > 1
                        ? null
                        : (int) GrupoMaster::where('id_tutor', $tutor->id)->value('id');
                    $request->session()->put('id_grupo', $idGrupo);
                }
            }
        }

        return redirect()->intended('/');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function assignIdGrupo(Request $request, $id_grupo)
    {
        if ($id_grupo) {
            $request->session()->put('id_grupo', (int) $id_grupo);

            return redirect('/site/index');
        }

        return redirect('/site/index')->with('error', 'El ID de grupo es inválido.');
    }

    public function clearIdGrupo(Request $request)
    {
        $request->session()->forget('id_grupo');

        return redirect('/select-grupo/index');
    }
}
