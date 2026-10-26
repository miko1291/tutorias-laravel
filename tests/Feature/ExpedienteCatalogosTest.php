<?php

namespace Tests\Feature;

use App\Http\Controllers\ExpedientePreguntaController;
use App\Models\Alumno;
use App\Models\AlumnoExpediente;
use App\Models\ExpedientePregunta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExpedienteCatalogosTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        $user = User::create(['username' => $role.uniqid(), 'email' => uniqid().'@example.com', 'auth_key' => str_repeat('a', 32), 'password_hash' => 'unused', 'status' => 10, 'created_at' => time(), 'updated_at' => time()]);
        DB::table('auth_assignment')->insert(['item_name' => $role, 'user_id' => (string) $user->id, 'created_at' => time()]);

        return $user;
    }

    private function payload(ExpedientePregunta $question, ?string $state = null): array
    {
        $options = $state ? ($question->configuracion['municipios_por_estado'][$state] ?? []) : $question->opciones;

        return ['revision' => hash('sha256', $question->toJson()), 'estado' => $state, 'etiquetas' => array_map(fn (string $value): string => $question->configuracion['etiquetas'][$value] ?? $value, $options)];
    }

    public function test_only_admin_can_manage_lists(): void
    {
        foreach (['alumno', 'tutor', 'psicologo'] as $role) {
            $this->actingAs($this->user($role))->get('/admin/expediente-catalogos')->assertForbidden();
            $this->post('/admin/expediente-catalogos/1', [])->assertForbidden();
        }
        $this->actingAs($this->user('admin'))->get('/admin/expediente-catalogos')->assertOk()->assertSee('Estado de procedencia');
        $this->get('/admin/expediente-catalogos?lista=campo_012&estado=Quintana%20Roo')->assertOk()->assertSee('Benito Juárez')->assertDontSee('value="Valladolid"', false);
    }

    public function test_region_is_loaded_once_and_preserves_customized_lists(): void
    {
        ExpedientePreguntaController::ensurePreguntasBase();
        $question = ExpedientePregunta::where('clave', 'campo_012')->firstOrFail();
        $maps = $question->configuracion['municipios_por_estado'];
        $this->assertCount(106, $maps['Yucatán']);
        $this->assertCount(11, $maps['Quintana Roo']);
        $this->assertCount(13, $maps['Campeche']);
        $this->assertCount(17, $maps['Tabasco']);
        $settings = $question->configuracion;
        $settings['municipios_por_estado']['Yucatán'][] = 'Localidad de prueba';
        $question->update(['configuracion' => $settings]);
        ExpedientePreguntaController::ensurePreguntasBase();
        $this->assertContains('Localidad de prueba', $question->fresh()->opcionesPara(['campo_010' => 'Yucatán']));
        $this->assertNotContains('Localidad de prueba', $question->fresh()->opcionesPara(['campo_010' => 'Campeche']));
    }

    public function test_admin_can_rename_options_and_add_localities_without_changing_saved_values(): void
    {
        $this->actingAs($this->user('admin'))->get('/admin/expediente-catalogos')->assertOk();
        $question = ExpedientePregunta::where('clave', 'campo_012')->firstOrFail();
        $payload = $this->payload($question, 'Yucatán');
        $index = array_search('Valladolid', $question->configuracion['municipios_por_estado']['Yucatán'], true);
        $payload['etiquetas'][$index] = 'Valladolid, Yucatán';
        $payload['nuevas'] = "Comisaría de prueba\nOtra localidad";
        $otherState = $question->configuracion['municipios_por_estado']['Campeche'];
        $this->post('/admin/expediente-catalogos/'.$question->id, $payload)->assertSessionHasNoErrors()->assertRedirect();
        $question->refresh();
        $this->assertSame('Valladolid, Yucatán', $question->configuracion['etiquetas']['Valladolid']);
        $this->assertContains('Comisaría de prueba', $question->opcionesPara(['campo_010' => 'Yucatán']));
        $this->assertSame($otherState, $question->configuracion['municipios_por_estado']['Campeche']);
        $this->post('/admin/expediente-catalogos/'.$question->id, $payload)->assertConflict();
    }

    public function test_lists_reject_duplicate_names_and_removing_options_used_in_conditions(): void
    {
        $this->actingAs($this->user('admin'))->get('/admin/expediente-catalogos');
        $question = ExpedientePregunta::where('clave', 'campo_010')->firstOrFail();
        $payload = $this->payload($question);
        $payload['nuevas'] = 'Yucatán';
        $this->post('/admin/expediente-catalogos/'.$question->id, $payload)->assertSessionHasErrors('nuevas');
        $payload = $this->payload($question);
        $payload['quitar'] = [array_search('Otro', $question->opciones, true)];
        $this->post('/admin/expediente-catalogos/'.$question->id, $payload)->assertSessionHasErrors('quitar');
        $this->assertSame($question->opciones, $question->fresh()->opciones);
    }

    public function test_student_cannot_submit_a_municipality_from_another_state_and_can_keep_a_historical_value(): void
    {
        ExpedientePreguntaController::ensurePreguntasBase();
        ExpedientePregunta::query()->update(['activo' => false]);
        ExpedientePregunta::whereIn('clave', ['campo_010', 'campo_012'])->update(['activo' => true, 'requerido' => true]);
        $user = $this->user('alumno');
        $student = Alumno::create(['nombres' => 'Ana', 'apellidop' => 'Prueba', 'apellidom' => 'Local', 'matricula' => 'GEO-01', 'telefono' => '9999999999', 'fecha_nac' => '2000-01-01', 'ciudad' => 'Valladolid', 'genero' => 1, 'correo' => 'ana@example.com', 'id_grupo' => 0, 'id_user' => $user->id]);
        $expediente = AlumnoExpediente::create(['id_alumno' => $student->id, 'estatus' => 'enviado', 'respuestas_json' => ['campo_010' => 'Yucatán', 'campo_012' => 'Localidad anterior']]);
        $this->actingAs($user)->postJson('/expediente/guardar', ['respuestas' => ['campo_010' => 'Quintana Roo', 'campo_012' => 'Valladolid']])->assertUnprocessable();
        $this->postJson('/expediente/guardar', ['respuestas' => ['campo_010' => 'Quintana Roo', 'campo_012' => 'Localidad anterior']])->assertUnprocessable();
        $this->get('/expediente/index')->assertOk()->assertSee('value="Localidad anterior" selected', false);
        $this->post('/expediente/guardar', ['respuestas' => ['campo_010' => 'Yucatán', 'campo_012' => 'Localidad anterior']])->assertRedirect('/expediente/ficha');
        $this->assertSame('Localidad anterior', $expediente->fresh()->getRespuestas()['campo_012']);
        $this->post('/expediente/guardar', ['respuestas' => ['campo_010' => 'Quintana Roo', 'campo_012' => 'Benito Juárez']])->assertRedirect('/expediente/ficha');
        $this->assertSame('Benito Juárez', $expediente->fresh()->getRespuestas()['campo_012']);
    }

    public function test_designer_saves_do_not_erase_municipality_associations(): void
    {
        $data = $this->actingAs($this->user('admin'))->get('/expediente-pregunta/index')->assertOk()->viewData('designerData');
        $payload = ['revision' => $data['revision'], 'secciones' => collect($data['preguntas'])->groupBy('seccion')->map(fn ($questions, $title) => ['titulo' => $title, 'preguntas' => $questions->values()->all()])->values()->all(), 'eliminadas' => []];
        $question = ExpedientePregunta::where('clave', 'campo_012')->firstOrFail();
        foreach ($payload['secciones'] as &$section) {
            foreach ($section['preguntas'] as &$definition) {
                if ($definition['clave'] === 'campo_012') {
                    $definition['configuracion']['municipios_por_estado'] = [];
                    $definition['opciones'] = ['No debe reemplazar los municipios'];
                }
            }
        }
        unset($section, $definition);
        $this->postJson('/expediente-pregunta/diseno', $payload)->assertOk();
        $this->assertSame($question->configuracion['municipios_por_estado'], $question->fresh()->configuracion['municipios_por_estado']);
        $this->assertSame($question->opciones, $question->fresh()->opciones);
    }
}
