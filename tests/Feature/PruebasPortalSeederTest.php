<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\AlumnoExpediente;
use App\Models\Asistencia;
use App\Models\GrupoMaster;
use App\Models\Notification;
use App\Models\User;
use Database\Seeders\PruebasPortalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PruebasPortalSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_scenarios_cover_submitted_draft_missing_and_scoped_groups(): void
    {
        Mail::fake();
        $this->seed(PruebasPortalSeeder::class);
        $this->assertDatabaseCount('user', 15);
        $this->assertDatabaseCount('alumno', 12);
        $this->assertDatabaseCount('grupo_master', 3);
        $this->assertDatabaseCount('asistencia', 24);
        $this->assertDatabaseCount('diagnostico', 3);
        $this->assertSame(6, Alumno::pendientesExpediente()->count());
        $this->assertSame(3, AlumnoExpediente::where('estatus', 'borrador')->count());
        $this->assertSame(3, Alumno::doesntHave('expedientes')->count());
        $user = User::where('username', 'prueba.tutor')->firstOrFail();
        $this->assertTrue(Hash::check(PruebasPortalSeeder::PASSWORD, $user->password_hash));
        $this->assertTrue(is_numeric($user->getRawOriginal('created_at')));
        $this->assertTrue(is_numeric($user->getRawOriginal('updated_at')));
        $this->actingAs($user)->get('/site/index')->assertOk()->assertViewHas('pendientesTutor', 4);
        $this->assertSame(2, GrupoMaster::where('id_tutor', $user->tutor->id)->count());
        $this->assertSame(27, Notification::count());
        Mail::assertNothingSent();
    }

    public function test_rerunning_does_not_duplicate_records_or_overwrite_user_edits(): void
    {
        $this->seed(PruebasPortalSeeder::class);
        $user = User::where('username', 'prueba.alumno')->firstOrFail();
        $hash = Hash::make('OtraClaveDePrueba123');
        $user->update(['password_hash' => $hash]);
        $expediente = AlumnoExpediente::firstOrFail();
        $expediente->update(['respuestas_json' => ['respuesta' => 'Edición conservada']]);
        Asistencia::firstOrFail()->update(['estado' => false]);
        $this->seed(PruebasPortalSeeder::class);
        $this->assertDatabaseCount('user', 15);
        $this->assertDatabaseCount('alumno_expediente', 9);
        $this->assertDatabaseCount('notification', 27);
        $this->assertSame($hash, $user->fresh()->password_hash);
        $this->assertSame(['respuesta' => 'Edición conservada'], $expediente->fresh()->respuestas_json);
        $this->assertFalse(Asistencia::firstOrFail()->estado);
    }
}
