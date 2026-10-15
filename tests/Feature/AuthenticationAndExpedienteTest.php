<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\ExpedientePregunta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationAndExpedienteTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_login_with_yii_username_and_password(): void
    {
        $user = $this->createUser('alumno01', 'secreta');

        $response = $this->post('/site/login', [
            'username' => 'alumno01',
            'password' => 'secreta',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    public function test_student_can_only_read_and_update_its_own_expediente(): void
    {
        $user = $this->createUser('alumno01', 'secreta');
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
            'id_grupo' => 1,
            'id_user' => $user->id,
        ]);

        $this->actingAs($user)
            ->get('/expediente/index')
            ->assertOk();

        $answers = [];
        foreach (ExpedientePregunta::activas() as $question) {
            if (! $question->requerido) {
                continue;
            }
            $answers[$question->clave] = match ($question->tipo) {
                'date' => '2000-01-01',
                'email' => 'ana@example.com',
                'tel' => '9999999999',
                'select', 'radio' => $question->opciones[0],
                default => 'Respuesta de prueba',
            };
        }
        $answers['idiomas'] = [];
        $this->post('/expediente/guardar', ['respuestas' => $answers])->assertRedirect('/expediente/ficha');

        $this->assertDatabaseHas('alumno_expediente', [
            'id_alumno' => $alumno->id,
            'estatus' => 'enviado',
        ]);
        $this->assertSame(['Ninguna'], json_decode(
            DB::table('alumno_expediente')->value('respuestas_json'),
            true
        )['idiomas']);
    }

    public function test_expediente_does_not_render_duplicate_blood_or_disability_questions(): void
    {
        $user = $this->createUser('alumno02', 'secreta');
        Alumno::create([
            'nombres' => 'Luis',
            'apellidop' => 'Pérez',
            'apellidom' => 'Gómez',
            'matricula' => 'A02',
            'correo' => 'luis@example.com',
            'telefono' => '9999999998',
            'fecha_nac' => '2001-01-01',
            'ciudad' => 'Valladolid',
            'genero' => 1,
            'id_grupo' => 1,
            'id_user' => $user->id,
        ]);

        $content = $this->actingAs($user)
            ->get('/expediente/index')
            ->assertOk()
            ->getContent();
        $this->assertSame(1, substr_count($content, 'name="respuestas[campo_022]"'));
        $this->assertStringNotContainsString('name="respuestas[tipo_sangre]"', $content);
        $this->assertStringNotContainsString('name="respuestas[discapacidad]"', $content);
    }

    public function test_only_admin_can_manage_expediente_questions(): void
    {
        $admin = $this->createUser('admin01', 'secreta');
        DB::table('auth_assignment')->insert([
            'item_name' => 'admin',
            'user_id' => (string) $admin->id,
            'created_at' => now()->timestamp,
        ]);

        $this->actingAs($admin)
            ->post('/expediente-pregunta/guardar', [
                'clave' => 'apoyo_academico',
                'titulo' => '¿Necesitas apoyo académico?',
                'tipo' => 'radio',
                'seccion' => 'Apoyo',
                'orden' => 10,
                'opciones' => "Sí\nNo",
                'activo' => 1,
            ])
            ->assertRedirect('/expediente-pregunta/index');

        $this->assertDatabaseHas('expediente_pregunta', [
            'clave' => 'apoyo_academico',
            'activo' => 1,
        ]);

        $psicologo = $this->createUser('psicologa01', 'secreta');
        DB::table('auth_assignment')->insert([
            'item_name' => 'psicologa',
            'user_id' => (string) $psicologo->id,
            'created_at' => now()->timestamp,
        ]);

        $this->actingAs($psicologo)
            ->get('/expediente-pregunta/index')
            ->assertOk()
            ->assertSee('Diseñar expediente');

        $segunda = ExpedientePregunta::create([
            'clave' => 'otra_pregunta',
            'titulo' => 'Otra pregunta',
            'tipo' => 'text',
            'seccion' => 'Apoyo',
            'orden' => 20,
            'activo' => true,
            'requerido' => false,
            'opciones_json' => '[]',
        ]);

        $this->actingAs($psicologo)
            ->post('/expediente-pregunta/ordenar', [
                'preguntas' => [
                    ['id' => $segunda->id, 'orden' => 1, 'seccion' => 'Apoyo'],
                    ['id' => ExpedientePregunta::where('clave', 'apoyo_academico')->value('id'), 'orden' => 2, 'seccion' => 'Apoyo'],
                ],
            ])
            ->assertRedirect('/expediente-pregunta/index');

        $this->assertDatabaseHas('expediente_pregunta', [
            'id' => $segunda->id,
            'orden' => 1,
        ]);

        $this->actingAs($this->createUser('tutor01', 'secreta'))
            ->get('/expediente-pregunta/index')
            ->assertForbidden();
    }

    public function test_question_can_be_created_without_an_internal_key_and_keeps_zero_options(): void
    {
        $admin = $this->createUser('editor-preguntas', 'secreta');
        DB::table('auth_assignment')->insert([
            'item_name' => 'admin', 'user_id' => (string) $admin->id, 'created_at' => now()->timestamp,
        ]);

        $this->actingAs($admin)->post('/expediente-pregunta/guardar', [
            'titulo' => 'Número de materias pendientes',
            'tipo' => 'select',
            'seccion' => 'Académica',
            'orden' => 1,
            'opciones' => "0\n1\n2",
            'activo' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect('/expediente-pregunta/index');

        $question = ExpedientePregunta::where('titulo', 'Número de materias pendientes')->firstOrFail();
        $this->assertMatchesRegularExpression('/^[a-z][a-z0-9_]{2,80}$/', $question->clave);
        $this->assertSame(['0', '1', '2'], $question->opciones);
    }

    public function test_clearing_the_internal_key_on_edit_preserves_existing_answers_reference(): void
    {
        $admin = $this->createUser('editor-claves', 'secreta');
        DB::table('auth_assignment')->insert([
            'item_name' => 'admin', 'user_id' => (string) $admin->id, 'created_at' => now()->timestamp,
        ]);
        $question = ExpedientePregunta::create([
            'clave' => 'clave_historica', 'titulo' => 'Pregunta original', 'tipo' => 'text',
            'seccion' => 'Apoyo', 'orden' => 1, 'activo' => true, 'requerido' => false,
        ]);

        $this->actingAs($admin)->post('/expediente-pregunta/guardar/'.$question->id, [
            'clave' => '', 'titulo' => 'Pregunta actualizada', 'tipo' => 'text',
            'seccion' => 'Apoyo', 'orden' => 1, 'activo' => 1,
        ])->assertSessionHasNoErrors();

        $this->assertSame('clave_historica', $question->fresh()->clave);
    }

    public function test_question_order_returns_json_for_the_editor_and_rejects_invalid_ids_atomically(): void
    {
        $admin = $this->createUser('editor-orden', 'secreta');
        DB::table('auth_assignment')->insert([
            'item_name' => 'admin', 'user_id' => (string) $admin->id, 'created_at' => now()->timestamp,
        ]);
        $question = ExpedientePregunta::create([
            'clave' => 'pregunta_orden', 'titulo' => 'Pregunta', 'tipo' => 'text',
            'seccion' => 'Original', 'orden' => 10, 'activo' => true, 'requerido' => false,
        ]);

        $this->actingAs($admin)->postJson('/expediente-pregunta/ordenar', ['preguntas' => [
            ['id' => $question->id, 'orden' => 1, 'seccion' => 'Nueva'],
            ['id' => 999999, 'orden' => 2, 'seccion' => 'Nueva'],
        ]])->assertUnprocessable();
        $this->assertSame('Original', $question->fresh()->seccion);

        $this->actingAs($admin)->postJson('/expediente-pregunta/ordenar', ['preguntas' => [
            ['id' => $question->id, 'orden' => 1, 'seccion' => 'Nueva'],
        ]])->assertOk()->assertJson(['message' => 'La estructura del expediente se guardó correctamente.']);
        $this->assertSame('Nueva', $question->fresh()->seccion);
    }

    private function createUser(string $username, string $password): User
    {
        return User::create([
            'username' => $username,
            'auth_key' => str_repeat('a', 32),
            'password_hash' => Hash::make($password),
            'email' => $username.'@example.com',
            'status' => User::STATUS_ACTIVE,
            'created_at' => now()->timestamp,
            'updated_at' => now()->timestamp,
        ]);
    }
}
