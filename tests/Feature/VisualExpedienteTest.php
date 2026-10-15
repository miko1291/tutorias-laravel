<?php

namespace Tests\Feature;

use App\Http\Controllers\ExpedientePreguntaController;
use App\Models\Alumno;
use App\Models\AlumnoExpediente;
use App\Models\ExpedientePregunta;
use App\Models\GrupoMaster;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VisualExpedienteTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        $user = User::create(['username' => $role.uniqid(), 'email' => uniqid().'@example.com', 'auth_key' => str_repeat('a', 32), 'password_hash' => 'unused', 'status' => 10, 'created_at' => time(), 'updated_at' => time()]);
        DB::table('auth_assignment')->insert(['item_name' => $role, 'user_id' => (string) $user->id, 'created_at' => time()]);

        return $user;
    }

    private function student(int $group, string $name): Alumno
    {
        return Alumno::create(['nombres' => $name, 'apellidop' => 'Prueba', 'apellidom' => 'Local', 'matricula' => $name, 'telefono' => '9999999999', 'fecha_nac' => '2000-01-01', 'ciudad' => 'Valladolid', 'genero' => 0, 'correo' => $name.'@example.com', 'id_grupo' => $group, 'id_user' => $this->user('alumno')->id]);
    }

    private function design(): array
    {
        return $this->get('/expediente-pregunta/index')->assertOk()->viewData('designerData');
    }

    private function payload(array $data): array
    {
        return ['revision' => $data['revision'], 'secciones' => collect($data['preguntas'])->groupBy('seccion')->map(fn ($questions, $title) => ['titulo' => $title, 'preguntas' => $questions->values()->all()])->values()->all(), 'eliminadas' => []];
    }

    public function test_navigation_has_one_designer_link_under_management(): void
    {
        $this->actingAs($this->user('admin'));
        $response = $this->get('/expediente-pregunta/index')->assertOk();
        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
        $xpath = new \DOMXPath($dom);
        $this->assertSame(1, $xpath->query('//nav//a[contains(@href,"expediente-pregunta/index")]')->length);
        $this->assertStringNotContainsString('/expediente/gestion', $response->getContent());
    }

    public function test_bulk_design_preserves_keys_and_rejects_stale_updates(): void
    {
        $this->actingAs($this->user('admin'));
        $data = $this->design();
        $payload = $this->payload($data);
        $key = $payload['secciones'][0]['preguntas'][0]['clave'];
        $payload['secciones'][0]['titulo'] = 'Mi primera sección';
        $payload['secciones'][0]['preguntas'][0]['titulo'] = 'Fecha revisada';
        $response = $this->postJson('/expediente-pregunta/diseno', $payload)->assertOk();
        $this->assertDatabaseHas('expediente_pregunta', ['clave' => $key, 'titulo' => 'Fecha revisada', 'seccion' => 'Mi primera sección', 'seccion_orden' => 1]);
        $this->postJson('/expediente-pregunta/diseno', $payload)->assertConflict();
        $payload['revision'] = $response->json('revision');
        $payload['secciones'][0]['preguntas'][0]['clave'] = 'changed_key';
        $this->postJson('/expediente-pregunta/diseno', $payload)->assertUnprocessable();
        $this->assertDatabaseMissing('expediente_pregunta', ['clave' => 'changed_key']);
    }

    public function test_archiving_and_restoring_keeps_historical_answers(): void
    {
        $this->actingAs($this->user('admin'));
        $data = $this->design();
        $payload = $this->payload($data);
        $question = array_pop($payload['secciones'][1]['preguntas']);
        $student = $this->student(0, 'Archivo');
        AlumnoExpediente::create(['id_alumno' => $student->id, 'version' => 1, 'estatus' => 'enviado', 'respuestas_json' => [$question['clave'] => 'Respuesta histórica']]);
        $payload['eliminadas'] = [$question['id']];
        $response = $this->postJson('/expediente-pregunta/diseno', $payload)->assertOk();
        ExpedientePreguntaController::ensurePreguntasBase();
        $this->assertSoftDeleted('expediente_pregunta', ['id' => $question['id']]);
        $this->assertSame('Respuesta histórica', $student->latestExpediente->getRespuestas()[$question['clave']]);
        $payload = $this->payload($response->json());
        $payload['secciones'][1]['preguntas'][] = $question;
        $this->postJson('/expediente-pregunta/diseno', $payload)->assertOk();
        $this->assertNotSoftDeleted('expediente_pregunta', ['id' => $question['id']]);
    }

    public function test_invalid_conditions_and_duplicate_questions_roll_back_entire_design(): void
    {
        $this->actingAs($this->user('admin'));
        $payload = $this->payload($this->design());
        $first = &$payload['secciones'][0]['preguntas'][0];
        $first['titulo'] = 'No debe guardarse';
        $first['configuracion']['condicion'] = ['clave' => 'campo_004', 'valor' => 'a', 'alternativa' => $first['clave']];
        $this->postJson('/expediente-pregunta/diseno', $payload)->assertUnprocessable();
        $this->assertDatabaseMissing('expediente_pregunta', ['titulo' => 'No debe guardarse']);
        unset($first['configuracion']['condicion']);
        $payload['secciones'][1]['preguntas'][] = $first;
        $this->postJson('/expediente-pregunta/diseno', $payload)->assertUnprocessable();
    }

    public function test_pending_students_include_drafts_and_missing_records_and_respect_tutor_groups(): void
    {
        $tutor = $this->user('tutor');
        $teacher = Tutor::create(['nombre' => 'Tutora', 'telefono' => '9999999999', 'genero' => 0, 'apellido' => 'Prueba', 'correo' => 'tutora@example.com', 'id_user' => $tutor->id]);
        $own = GrupoMaster::create(['id_tutor' => $teacher->id, 'id_periodo' => 1, 'id_carrera' => 1, 'id_semestre' => 1, 'id_grupoLetra' => 1]);
        $other = GrupoMaster::create(['id_tutor' => 999, 'id_periodo' => 1, 'id_carrera' => 1, 'id_semestre' => 1, 'id_grupoLetra' => 1]);
        $missing = $this->student($own->id, 'SinEnvio');
        $draft = $this->student($own->id, 'Borrador');
        $sent = $this->student($own->id, 'YaEnviado');
        $outside = $this->student($other->id, 'OtroGrupo');
        $unassigned = $this->student(0, 'SinGrupo');
        AlumnoExpediente::create(['id_alumno' => $draft->id, 'version' => 1, 'estatus' => 'enviado', 'respuestas_json' => []]);
        AlumnoExpediente::create(['id_alumno' => $draft->id, 'version' => 2, 'estatus' => 'borrador', 'respuestas_json' => []]);
        AlumnoExpediente::create(['id_alumno' => $sent->id, 'version' => 1, 'estatus' => 'enviado', 'respuestas_json' => []]);
        $response = $this->actingAs($tutor)->get('/grupo-master/pendientes')->assertOk();
        $this->assertEqualsCanonicalizing([$missing->id, $draft->id], $response->viewData('pendientes')->pluck('id')->all());
        $this->get('/grupo-master/pendientes/'.$other->id)->assertForbidden();
        $response = $this->actingAs($this->user('admin'))->get('/grupo-master/pendientes')->assertOk();
        $this->assertEqualsCanonicalizing([$missing->id, $draft->id, $outside->id, $unassigned->id], $response->viewData('pendientes')->pluck('id')->all());
        $this->actingAs($this->user('alumno'))->get('/grupo-master/pendientes')->assertForbidden();
    }

    public function test_student_validation_uses_the_design_and_ignores_hidden_answers(): void
    {
        ExpedientePreguntaController::ensurePreguntasBase();
        ExpedientePregunta::query()->update(['activo' => false]);
        $parent = ExpedientePregunta::create(['clave' => 'necesita_apoyo', 'titulo' => '¿Necesitas apoyo?', 'tipo' => 'radio', 'seccion' => 'Apoyo', 'activo' => true, 'requerido' => true, 'opciones_json' => '["Si","No"]']);
        $detail = ExpedientePregunta::create(['clave' => 'detalle_apoyo', 'titulo' => '¿Cuál?', 'tipo' => 'checkbox', 'seccion' => 'Apoyo', 'activo' => true, 'requerido' => true, 'opciones_json' => '["0","1"]', 'configuracion' => ['condicion' => ['clave' => $parent->clave, 'valor' => 'Si']]]);
        $student = $this->student(0, 'Validacion');
        $this->actingAs(User::findOrFail($student->id_user));
        $this->get('/expediente/index')->assertOk()->assertSee('¿Necesitas apoyo?');
        $this->postJson('/expediente/guardar', ['respuestas' => ['necesita_apoyo' => 'Si', 'detalle_apoyo' => []]])->assertUnprocessable();
        $this->postJson('/expediente/guardar', ['respuestas' => ['necesita_apoyo' => 'Si', 'detalle_apoyo' => ['invalid']]])->assertUnprocessable();
        $this->post('/expediente/guardar', ['respuestas' => ['necesita_apoyo' => 'Si', 'detalle_apoyo' => ['0']]])->assertRedirect('/expediente/ficha');
        $this->assertSame(['0'], $student->latestExpediente->getRespuestas()['detalle_apoyo']);
        $this->post('/expediente/guardar', ['respuestas' => ['necesita_apoyo' => 'No', 'detalle_apoyo' => ['forged']]])->assertRedirect('/expediente/ficha');
        $this->assertSame([], $student->fresh()->latestExpediente->getRespuestas()['detalle_apoyo']);
    }
}
