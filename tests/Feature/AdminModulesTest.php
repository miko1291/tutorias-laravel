<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Carreras;
use App\Models\GrupoLetra;
use App\Models\GrupoMaster;
use App\Models\PeriodoEscolar;
use App\Models\Semestre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_user_and_catalog_record(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin)
            ->post('/admin/users/save', [
                'username' => 'tutor01',
                'email' => 'tutor@example.com',
                'password' => 'password123',
                'status' => 10,
                'role' => 'tutor',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('user', ['username' => 'tutor01']);
        $this->assertDatabaseHas('auth_assignment', ['item_name' => 'tutor', 'user_id' => '2']);

        $this->actingAs($admin)
            ->post('/admin/catalogos/carreras/save', ['nombre' => 'Ingeniería'])
            ->assertRedirect();

        $this->assertDatabaseHas('carreras', ['nombre' => 'Ingeniería']);
    }

    public function test_non_admin_cannot_access_administration(): void
    {
        $user = User::create([
            'username' => 'tutor01',
            'auth_key' => str_repeat('a', 32),
            'password_hash' => Hash::make('secreta'),
            'email' => 'tutor@example.com',
            'status' => User::STATUS_ACTIVE,
            'created_at' => now()->timestamp,
            'updated_at' => now()->timestamp,
        ]);
        DB::table('auth_assignment')->insert([
            'item_name' => 'tutor',
            'user_id' => (string) $user->id,
            'created_at' => now()->timestamp,
        ]);

        $this->actingAs($user)->get('/admin/users')->assertForbidden();
    }

    public function test_admin_can_edit_group_and_assign_student(): void
    {
        $admin = $this->createAdmin();
        $periodo = PeriodoEscolar::find(DB::table('periodo_escolar')->insertGetId([
            'nombre' => 'Periodo de prueba',
            'id_ciclo' => 1,
            'id_estatus' => 1,
            'letra_periodo' => 'A',
            'date_start' => '2026-01-01',
            'date_end' => '2026-06-30',
            'created_at' => now(),
            'updated_at' => now(),
        ]));
        $carrera = Carreras::create(['nombre' => 'Sistemas']);
        $semestre = Semestre::create(['nombre' => 'Primer semestre', 'num_semestre' => 1]);
        $letra = GrupoLetra::create(['letra_key' => 'A']);
        $group = GrupoMaster::create([
            'id_periodo' => $periodo->id,
            'id_carrera' => $carrera->id,
            'id_semestre' => $semestre->id,
            'id_grupoLetra' => $letra->id,
        ]);
        $student = Alumno::create([
            'nombres' => 'Estudiante',
            'apellidop' => 'Sin',
            'apellidom' => 'Grupo',
            'matricula' => 'SIN-001',
            'correo' => 'sin-grupo@example.com',
            'telefono' => '9999999999',
            'fecha_nac' => '2000-01-01',
            'ciudad' => 'Valladolid',
            'genero' => 0,
            'id_grupo' => 0,
        ]);

        $this->actingAs($admin)
            ->get('/admin/groups?edit='.$group->id)
            ->assertOk()
            ->assertSee('Alumnos del grupo');

        $this->actingAs($admin)
            ->post('/admin/groups/'.$group->id.'/assign-student', ['id_alumno' => $student->id])
            ->assertRedirect('/admin/groups?edit='.$group->id);

        $this->assertDatabaseHas('alumno', ['id' => $student->id, 'id_grupo' => $group->id]);

        $this->actingAs($admin)
            ->post('/admin/groups/'.$group->id.'/students/'.$student->id.'/unassign')
            ->assertRedirect('/admin/groups?edit='.$group->id);

        $this->assertDatabaseHas('alumno', ['id' => $student->id, 'id_grupo' => 0]);

        $this->actingAs($admin)
            ->post('/admin/groups/'.$group->id.'/delete')
            ->assertRedirect();

        $this->assertDatabaseMissing('grupo_master', ['id' => $group->id]);
    }

    private function createAdmin(): User
    {
        $user = User::create([
            'username' => 'admin01',
            'auth_key' => str_repeat('a', 32),
            'password_hash' => Hash::make('secreta'),
            'email' => 'admin@example.com',
            'status' => User::STATUS_ACTIVE,
            'created_at' => now()->timestamp,
            'updated_at' => now()->timestamp,
        ]);
        DB::table('auth_assignment')->insert([
            'item_name' => 'admin',
            'user_id' => (string) $user->id,
            'created_at' => now()->timestamp,
        ]);

        return $user;
    }
}
