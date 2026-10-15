<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Carreras;
use App\Models\CicloEscolar;
use App\Models\Criterio;
use App\Models\Estatus;
use App\Models\GrupoLetra;
use App\Models\GrupoMaster;
use App\Models\Pat;
use App\Models\Performance;
use App\Models\PeriodoEscolar;
use App\Models\Semana;
use App\Models\Semestre;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        return view('admin.index', [
            'usersCount' => User::count(),
            'studentsCount' => Alumno::count(),
            'tutorsCount' => Tutor::count(),
            'groupsCount' => GrupoMaster::count(),
            'pendingExpedientes' => Alumno::pendientesExpediente()->count(),
        ]);
    }

    public function users(Request $request): View
    {
        $users = User::orderBy('username')->paginate(30);
        $roles = DB::table('auth_assignment')
            ->whereIn('user_id', $users->getCollection()->pluck('id')->map(static fn (int $id): string => (string) $id))
            ->get()
            ->groupBy('user_id')
            ->map(static fn ($assignments): string => $assignments->pluck('item_name')->implode(', '));

        return view('admin.users', [
            'users' => $users,
            'roles' => $roles,
            'editUser' => $request->integer('edit') ? User::findOrFail($request->integer('edit')) : null,
        ]);
    }

    public function saveUser(Request $request, ?int $id = null): RedirectResponse
    {
        $user = $id ? User::findOrFail($id) : new User;
        $data = $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:user,username'.($id ? ','.$id : '')],
            'email' => ['required', 'email', 'max:255'],
            'status' => ['required', 'integer', 'in:0,9,10'],
            'role' => ['required', 'string', 'max:64'],
            'password' => [$id ? 'nullable' : 'required', 'string', 'min:8'],
        ]);
        $user->fill($data);
        if ($data['password'] ?? null) {
            $user->password_hash = Hash::make($data['password']);
        }
        if (! $id) {
            $user->auth_key = str_repeat('a', 32);
        }

        DB::transaction(function () use ($user, $data): void {
            $user->save();
            DB::table('auth_assignment')->where('user_id', (string) $user->id)->delete();
            DB::table('auth_assignment')->insert([
                'item_name' => $data['role'],
                'user_id' => (string) $user->id,
                'created_at' => now()->timestamp,
            ]);
        });

        return back()->with('success', 'Usuario guardado correctamente.');
    }

    public function deleteUser(int $id): RedirectResponse
    {
        User::findOrFail($id)->update(['status' => User::STATUS_DELETED]);

        return back()->with('success', 'Usuario desactivado correctamente.');
    }

    public function tutors(Request $request): View
    {
        return view('admin.tutors', [
            'tutors' => Tutor::with('user')->withCount('grupoMasters')->orderBy('apellido')->paginate(30),
            'users' => User::where('status', User::STATUS_ACTIVE)->orderBy('username')->get(),
            'editTutor' => $request->integer('edit') ? Tutor::findOrFail($request->integer('edit')) : null,
        ]);
    }

    public function saveTutor(Request $request, ?int $id = null): RedirectResponse
    {
        $tutor = $id ? Tutor::findOrFail($id) : new Tutor;
        $tutor->fill($request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:45'],
            'correo' => ['required', 'email', 'max:100'],
            'telefono' => ['required', 'string', 'max:20'],
            'genero' => ['required', 'integer', 'in:0,1'],
            'id_user' => ['required', 'integer', 'exists:user,id'],
        ]))->save();

        return back()->with('success', 'Tutor guardado correctamente.');
    }

    public function deleteTutor(int $id): RedirectResponse
    {
        Tutor::findOrFail($id)->delete();

        return back()->with('success', 'Tutor eliminado correctamente.');
    }

    public function catalog(string $catalog): View
    {
        [$model, $title, $fields] = $this->catalogDefinition($catalog);

        return view('admin.catalog', [
            'catalog' => $catalog,
            'title' => $title,
            'fields' => $fields,
            'items' => $model::orderBy('id')->paginate(30),
        ]);
    }

    public function saveCatalog(Request $request, string $catalog, ?int $id = null): RedirectResponse
    {
        [$model, , $fields] = $this->catalogDefinition($catalog);
        $item = $id ? $model::findOrFail($id) : new $model;
        $item->fill($request->validate($fields))->save();

        return back()->with('success', 'Catálogo actualizado correctamente.');
    }

    public function deleteCatalog(string $catalog, int $id): RedirectResponse
    {
        [$model] = $this->catalogDefinition($catalog);
        $model::findOrFail($id)->delete();

        return back()->with('success', 'Registro eliminado correctamente.');
    }

    public function groups(Request $request): View
    {
        $editGroup = $request->integer('edit') ? GrupoMaster::findOrFail($request->integer('edit')) : null;

        return view('admin.groups', [
            'groups' => GrupoMaster::with(['periodo', 'carrera', 'semestre', 'grupoLetra', 'tutor'])
                ->withCount('alumnos')
                ->orderByDesc('id')
                ->paginate(30),
            'periodos' => PeriodoEscolar::orderBy('id')->get(),
            'carreras' => Carreras::orderBy('id')->get(),
            'semestres' => Semestre::orderBy('id')->get(),
            'letras' => GrupoLetra::orderBy('id')->get(),
            'tutores' => Tutor::orderBy('apellido')->get(),
            'editGroup' => $editGroup,
            'groupStudents' => $editGroup
                ? Alumno::where('id_grupo', $editGroup->id)->orderBy('apellidop')->orderBy('nombres')->get()
                : collect(),
            'availableStudents' => Alumno::where(function ($query): void {
                $query->whereNull('id_grupo')->orWhere('id_grupo', 0);
            })->orderBy('apellidop')->orderBy('nombres')->get(),
        ]);
    }

    public function saveGroup(Request $request, ?int $id = null): RedirectResponse
    {
        $group = $id ? GrupoMaster::findOrFail($id) : new GrupoMaster;
        $group->fill($request->validate([
            'id_periodo' => ['required', 'integer', 'exists:periodo_escolar,id'],
            'id_carrera' => ['required', 'integer', 'exists:carreras,id'],
            'id_semestre' => ['required', 'integer', 'exists:semestre,id'],
            'id_grupoLetra' => ['required', 'integer', 'exists:grupo_letra,id'],
            'id_tutor' => ['nullable', 'integer', 'exists:tutor,id'],
        ]))->save();

        return back()->with('success', 'Grupo guardado correctamente.');
    }

    public function assignStudent(Request $request, int $id): RedirectResponse
    {
        GrupoMaster::findOrFail($id);
        $data = $request->validate([
            'id_alumno' => ['required', 'integer', 'exists:alumno,id'],
        ]);

        Alumno::whereKey($data['id_alumno'])->update(['id_grupo' => $id]);

        return redirect('/admin/groups?edit='.$id)
            ->with('success', 'Alumno asignado al grupo correctamente.');
    }

    public function unassignStudent(int $id, int $idAlumno): RedirectResponse
    {
        $group = GrupoMaster::findOrFail($id);
        Alumno::whereKey($idAlumno)->where('id_grupo', $group->id)->update(['id_grupo' => 0]);

        return redirect('/admin/groups?edit='.$id)
            ->with('success', 'Alumno retirado del grupo. Sus datos se conservaron.');
    }

    public function deleteGroup(int $id): RedirectResponse
    {
        DB::transaction(function () use ($id): void {
            $group = GrupoMaster::findOrFail($id);
            Alumno::where('id_grupo', $group->id)->update(['id_grupo' => 0]);
            $group->delete();
        });

        return back()->with('success', 'Grupo eliminado correctamente. Los alumnos quedaron sin grupo.');
    }

    public function pats(): View
    {
        return view('admin.program', [
            'title' => 'Programas de acción tutorial',
            'kind' => 'pat',
            'items' => Pat::with('semestre')->orderBy('id')->paginate(30),
            'semestres' => Semestre::orderBy('id')->get(),
        ]);
    }

    public function savePat(Request $request, ?int $id = null): RedirectResponse
    {
        $pat = $id ? Pat::findOrFail($id) : new Pat;
        $pat->fill($request->validate([
            'id_semestre' => ['required', 'integer', 'exists:semestre,id'],
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'estatus' => ['required', 'integer', 'in:0,1'],
        ]))->save();

        return back()->with('success', 'PAT guardado correctamente.');
    }

    public function deletePat(int $id): RedirectResponse
    {
        Pat::findOrFail($id)->delete();

        return back()->with('success', 'PAT eliminado correctamente.');
    }

    public function semanasAdmin(): View
    {
        return view('admin.program', [
            'title' => 'Semanas del PAT',
            'kind' => 'semana',
            'items' => Semana::with('pat')->orderBy('id')->paginate(30),
            'pats' => Pat::orderBy('id')->get(),
        ]);
    }

    public function saveSemana(Request $request, ?int $id = null): RedirectResponse
    {
        $semana = $id ? Semana::findOrFail($id) : new Semana;
        $semana->fill($request->validate([
            'num_semana' => ['required', 'integer', 'min:1'],
            'nombre' => ['required', 'string', 'max:100'],
            'tipo_tutoria' => ['required', 'integer'],
            'tematica' => ['required', 'string'],
            'objetivos' => ['required', 'string'],
            'justificacion' => ['required', 'string'],
            'estrategias_tutor' => ['required', 'string'],
            'acciones' => ['required', 'string'],
            'estrategias_tutorado' => ['nullable', 'string'],
            'id_pat' => ['required', 'integer', 'exists:pat,id'],
        ]))->save();

        return back()->with('success', 'Semana guardada correctamente.');
    }

    public function deleteSemana(int $id): RedirectResponse
    {
        Semana::findOrFail($id)->delete();

        return back()->with('success', 'Semana eliminada correctamente.');
    }

    public function performances(): View
    {
        return view('admin.program', [
            'title' => 'Desempeño de grupos',
            'kind' => 'performance',
            'items' => Performance::with('grupo')->orderByDesc('id')->paginate(30),
            'groups' => GrupoMaster::with(['carrera', 'semestre', 'grupoLetra'])->orderBy('id')->get(),
        ]);
    }

    public function savePerformance(Request $request, ?int $id = null): RedirectResponse
    {
        $performance = $id ? Performance::findOrFail($id) : new Performance;
        $performance->fill($request->validate([
            'id_grupo' => ['required', 'integer', 'exists:grupo_master,id'],
            'eDesempeño' => ['required', 'integer', 'min:0'],
            'bDesempeño' => ['required', 'integer', 'min:0'],
            'arDesempeño' => ['required', 'integer', 'min:0'],
            'comentarios' => ['nullable', 'string'],
        ]))->save();

        return back()->with('success', 'Desempeño guardado correctamente.');
    }

    public function deletePerformance(int $id): RedirectResponse
    {
        Performance::findOrFail($id)->delete();

        return back()->with('success', 'Desempeño eliminado correctamente.');
    }

    private function catalogDefinition(string $catalog): array
    {
        return match ($catalog) {
            'carreras' => [Carreras::class, 'Carreras', ['nombre' => ['required', 'string', 'max:150']]],
            'estatus' => [Estatus::class, 'Estatus', ['nombre' => ['required', 'string', 'max:50']]],
            'grupo-letra' => [GrupoLetra::class, 'Letras de grupo', ['letra_key' => ['required', 'string', 'max:2']]],
            'semestres' => [Semestre::class, 'Semestres', ['nombre' => ['required', 'string', 'max:50'], 'num_semestre' => ['required', 'integer', 'min:1']]],
            'criterios' => [Criterio::class, 'Criterios', ['nombre' => ['required', 'string']]],
            'ciclos' => [CicloEscolar::class, 'Ciclos escolares', ['nombre' => ['required', 'string', 'max:100'], 'fecha_inicial' => ['required', 'date'], 'fecha_final' => ['required', 'date'], 'id_estatus' => ['required', 'integer', 'exists:estatus,id']]],
            'periodos' => [PeriodoEscolar::class, 'Periodos escolares', ['nombre' => ['required', 'string', 'max:50'], 'id_estatus' => ['required', 'integer', 'exists:estatus,id'], 'letra_periodo' => ['required', 'string', 'max:2'], 'date_start' => ['required', 'date'], 'date_end' => ['required', 'date'], 'id_ciclo' => ['required', 'integer', 'exists:ciclo_escolar,id']]],
            default => abort(404),
        };
    }
}
