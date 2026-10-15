<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\AlumnoExpediente;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoleDashboardsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_role_takes_priority_over_student_role(): void
    {
        $admin = $this->createUser('admin.local', ['admin', 'alumno']);

        $this->actingAs($admin)
            ->get('/')
            ->assertRedirect('/admin/index');

        $this->actingAs($admin)
            ->get('/admin/index')
            ->assertOk()
            ->assertSee('Panel administrativo');
    }

    public function test_admin_navigation_contains_the_administrative_modules(): void
    {
        $admin = $this->createUser('admin-navigation', ['admin']);

        $this->actingAs($admin)
            ->get('/admin/index')
            ->assertOk()
            ->assertSee('/admin/users', false)
            ->assertSee('/admin/tutors', false)
            ->assertSee('/admin/groups', false)
            ->assertSee('/admin/pat', false)
            ->assertSee('/admin/catalogos/carreras', false)
            ->assertSee('/expediente-pregunta/index', false);
    }

    public function test_administrative_dropdowns_have_a_positioned_parent(): void
    {
        $admin = $this->createUser('admin-dropdowns', ['admin']);
        $response = $this->actingAs($admin)->get('/expediente-pregunta/index')->assertOk();
        $document = new \DOMDocument;
        @$document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
        $xpath = new \DOMXPath($document);
        $toggles = $xpath->query('//nav//a[@data-toggle="dropdown"]');

        $this->assertGreaterThan(0, $toggles->length);
        foreach ($toggles as $toggle) {
            $classes = explode(' ', $toggle->parentNode->getAttribute('class'));
            $this->assertContains('dropdown', $classes, 'El menú '.$toggle->getAttribute('id').' debe estar anclado a su botón.');
        }
    }

    public function test_student_can_open_profile_and_expediente(): void
    {
        $student = $this->createUser('alumno.local', ['alumno']);
        Alumno::create([
            'nombres' => 'Alumno',
            'apellidop' => 'De',
            'apellidom' => 'Prueba',
            'matricula' => 'DEMO-01',
            'correo' => 'alumno@example.com',
            'telefono' => '9999999999',
            'fecha_nac' => '2000-01-01',
            'ciudad' => 'Valladolid',
            'genero' => 0,
            'id_grupo' => 0,
            'id_user' => $student->id,
        ]);

        $this->actingAs($student)->get('/expediente/perfil')->assertOk()->assertSee('Mi perfil');
        $this->actingAs($student)->get('/expediente/ficha')->assertOk()->assertSee('Mi ficha de expediente');
        $this->actingAs($student)->get('/expediente/index')->assertOk()->assertSee('Expediente');
    }

    public function test_individual_expediente_management_is_removed_for_all_roles(): void
    {
        $student = $this->createUser('student-expediente', ['alumno']);
        $alumno = Alumno::create([
            'nombres' => 'Estudiante',
            'apellidop' => 'Gestionable',
            'apellidom' => 'Prueba',
            'matricula' => 'GEST-01',
            'correo' => 'gestionable@example.com',
            'telefono' => '9999999997',
            'fecha_nac' => '2000-01-01',
            'ciudad' => 'Valladolid',
            'genero' => 0,
            'id_grupo' => 0,
            'id_user' => $student->id,
        ]);
        AlumnoExpediente::create([
            'id_alumno' => $alumno->id,
            'version' => 1,
            'respuestas_json' => ['campo_004' => 'old@example.com'],
            'estatus' => AlumnoExpediente::ESTATUS_ENVIADO,
        ]);

        foreach (['admin', 'psicologo', 'tutor', 'alumno'] as $role) {
            $user = $this->createUser($role.'-expediente', [$role]);
            $this->actingAs($user)->get('/expediente/gestion')->assertNotFound();
            $this->get('/expediente/gestion/editar/'.$alumno->id)->assertNotFound();
            $this->post('/expediente/gestion/guardar/'.$alumno->id, [
                'respuestas' => ['campo_004' => 'changed@example.com'],
            ])->assertNotFound();
        }
        $this->assertSame('old@example.com', $alumno->latestExpediente->getRespuestas()['campo_004']);
    }

    public function test_tutor_can_open_profile(): void
    {
        $tutorUser = $this->createUser('tutor.local', ['tutor']);
        Tutor::create([
            'nombre' => 'Tutor',
            'apellido' => 'De Prueba',
            'correo' => 'tutor@example.com',
            'telefono' => '9999999998',
            'genero' => 0,
            'id_user' => $tutorUser->id,
        ]);

        $this->actingAs($tutorUser)
            ->get('/tutor/index')
            ->assertOk()
            ->assertSee('Perfil del tutor');
    }

    public function test_tutor_profile_links_to_group_selection_route(): void
    {
        $tutorUser = $this->createUser('tutor-with-group', ['tutor']);
        $tutor = Tutor::create([
            'nombre' => 'Tutor',
            'apellido' => 'Con Grupo',
            'correo' => 'tutor-group@example.com',
            'telefono' => '9999999998',
            'genero' => 0,
            'id_user' => $tutorUser->id,
        ]);
        DB::table('carreras')->insert(['nombre' => 'Sistemas']);
        DB::table('semestre')->insert(['nombre' => 'Primero', 'num_semestre' => 1]);
        DB::table('grupo_letra')->insert(['letra_key' => 'A']);
        DB::table('ciclo_escolar')->insert([
            'nombre' => 'Ciclo',
            'fecha_inicial' => '2026-01-01',
            'fecha_final' => '2026-12-31',
            'id_estatus' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('periodo_escolar')->insert([
            'nombre' => 'Periodo',
            'id_ciclo' => 1,
            'id_estatus' => 1,
            'letra_periodo' => 'A',
            'date_start' => '2026-01-01',
            'date_end' => '2026-06-30',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $group = DB::table('grupo_master')->insertGetId([
            'id_periodo' => 1,
            'id_carrera' => 1,
            'id_semestre' => 1,
            'id_grupoLetra' => 1,
            'id_tutor' => $tutor->id,
        ]);

        $this->actingAs($tutorUser)
            ->get('/tutor/index')
            ->assertOk()
            ->assertSee('/site/assign-id-grupo/'.$group, false);
    }

    private function createUser(string $username, array $roles): User
    {
        $user = User::create([
            'username' => $username,
            'auth_key' => str_repeat('a', 32),
            'password_hash' => Hash::make('password123'),
            'email' => $username.'@example.com',
            'status' => User::STATUS_ACTIVE,
            'created_at' => now()->timestamp,
            'updated_at' => now()->timestamp,
        ]);

        foreach ($roles as $role) {
            DB::table('auth_assignment')->insert([
                'item_name' => $role,
                'user_id' => (string) $user->id,
                'created_at' => now()->timestamp,
            ]);
        }

        return $user;
    }
}
