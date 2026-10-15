<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\GrupoMaster;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProgramaModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_register_and_export_attendance(): void
    {
        $admin = $this->createAdmin();
        $grupo = GrupoMaster::create([
            'id_periodo' => 1,
            'id_carrera' => 1,
            'id_semestre' => 1,
            'id_grupoLetra' => 1,
        ]);
        $alumno = Alumno::create([
            'nombres' => 'Ana',
            'apellidop' => 'López',
            'apellidom' => 'Pérez',
            'matricula' => 'A01',
            'correo' => 'ana@example.com',
            'telefono' => '9999999999',
            'fecha_nac' => '2000-01-01',
            'ciudad' => 'Valladolid',
            'genero' => 1,
            'id_grupo' => $grupo->id,
        ]);

        $this->actingAs($admin)->post("/asistencia/tomar-lista/{$grupo->id}", [
            'fecha' => '2026-09-30',
            'estado' => [$alumno->id => 1],
        ])->assertRedirect();

        $this->assertDatabaseHas('asistencia', [
            'id_alumno' => $alumno->id,
            'id_grupo' => $grupo->id,
            'estado' => 1,
        ]);

        $this->actingAs($admin)
            ->get("/asistencia/exportar-reporte/{$grupo->id}")
            ->assertOk()
            ->assertHeader('Content-Disposition', 'attachment; filename=reporte_asistencia.csv');
    }

    public function test_admin_can_register_semana_real_with_evidence(): void
    {
        Storage::fake('local');
        $admin = $this->createAdmin();
        $grupo = GrupoMaster::create([
            'id_periodo' => 1,
            'id_carrera' => 1,
            'id_semestre' => 1,
            'id_grupoLetra' => 1,
        ]);
        $pat = DB::table('pat')->insertGetId([
            'id_semestre' => 1,
            'nombre' => 'PAT 2026',
            'descripcion' => 'Programa',
            'estatus' => 1,
        ]);
        $semana = DB::table('semana')->insertGetId([
            'num_semana' => 1,
            'nombre' => 'Semana 1',
            'tipo_tutoria' => 1,
            'tematica' => 'Inicio',
            'objetivos' => 'Objetivo',
            'justificacion' => 'Justificación',
            'estrategias_tutor' => 'Estrategia',
            'acciones' => 'Acciones',
            'estrategias_tutorado' => null,
            'id_pat' => $pat,
        ]);

        $this->actingAs($admin)->post("/semana-real/create/{$semana}/{$grupo->id}", [
            'semana_atendida' => 1,
            'alumnos_atendidos' => 1,
            'alumnos_faltantes' => 0,
            'total_alumnos' => 1,
            'atendidos_hombres' => 0,
            'atendidos_mujeres' => 1,
            'total_gatendidos' => 1,
            'observaciones' => 'Sin incidencias',
            'evidencias' => [UploadedFile::fake()->image('evidencia.jpg')],
        ])->assertRedirect();

        $this->assertDatabaseHas('semana_real', [
            'id_semana' => $semana,
            'id_grupomaster' => $grupo->id,
        ]);
        $this->assertNotEmpty(DB::table('semana_real')->value('evidencias'));
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
