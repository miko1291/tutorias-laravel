<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\AlumnoExpediente;
use App\Models\GrupoMaster;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PortalExperienceTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        $user = User::create(['username' => $role.uniqid(), 'email' => uniqid().'@example.com', 'auth_key' => str_repeat('a', 32), 'password_hash' => 'unused', 'status' => 10, 'created_at' => time(), 'updated_at' => time()]);
        DB::table('auth_assignment')->insert(['item_name' => $role, 'user_id' => (string) $user->id, 'created_at' => time()]);

        return $user;
    }

    public function test_access_and_login_offer_all_roles_without_changing_permissions(): void
    {
        $this->get('/site/acceso')->assertOk()->assertDontSee('/site/login?perfil=psicologo', false)->assertSee('/manual', false);
        $this->get('/site/login?perfil=psicologo')->assertOk()->assertSee('Acceso para psicología')->assertSee('autocomplete="current-password"', false);
        $this->get('/site/login?perfil=unknown')->assertOk()->assertSee('Acceso para alumnos');
        $this->get('/site/login?perfil[]=admin')->assertOk()->assertSee('Acceso para alumnos');
    }

    public function test_manuals_explain_current_user_actions_for_every_role(): void
    {
        foreach (['alumno' => 'Enviar expediente', 'tutor' => 'Notificar al grupo', 'admin' => 'Guardar diseño', 'psicologo' => 'Vista de alumno'] as $role => $action) {
            $response = $this->get('/manual?perfil='.$role)->assertOk()->assertSee($action)->assertSee('Imprimir o guardar PDF');
            $dom = new \DOMDocument;
            @$dom->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
            $xpath = new \DOMXPath($dom);
            foreach ($xpath->query('//nav[@class="guide-toc"]/a') as $link) {
                $this->assertSame(1, $xpath->query('//*[@id="'.substr($link->getAttribute('href'), 1).'"]')->length);
            }
        }
        $this->get('/manual?perfil[]=admin')->assertOk();
    }

    public function test_manual_defaults_to_the_authenticated_role(): void
    {
        $this->actingAs($this->user('admin'))->get('/manual')->assertOk()->assertSee('Manual del administrador');
        $this->actingAs($this->user('psicologa'))->get('/manual')->assertOk()->assertSee('Manual de psicología');
    }

    public function test_students_cannot_select_other_manuals_while_staff_can_browse_all(): void
    {
        $this->actingAs($this->user('alumno'))->get('/manual?perfil=admin')->assertOk()->assertSee('Manual del alumno')->assertDontSee('Manual del administrador')->assertDontSee('/manual?perfil=tutor', false);
        foreach (['tutor', 'admin', 'psicologa'] as $role) {
            $this->actingAs($this->user($role))->get('/manual?perfil=admin')->assertOk()->assertSee('Manual del administrador')->assertSee('/manual?perfil=alumno', false)->assertSee('/manual?perfil=tutor', false);
        }
    }

    public function test_administrative_home_exposes_the_current_workflows(): void
    {
        $response = $this->actingAs($this->user('admin'))->get('/admin/index')->assertOk()
            ->assertSee('Revisar pendientes')->assertSee('Diseñar expediente')->assertSee('/manual?perfil=admin', false);
    }

    public function test_student_home_treats_approved_expediente_as_sent_and_form_has_one_heading(): void
    {
        $user = $this->user('alumno');
        $student = Alumno::create(['nombres' => 'Ana', 'apellidop' => 'Prueba', 'apellidom' => 'Local', 'matricula' => 'PRUEBA-01', 'correo' => 'ana@example.com', 'telefono' => '9999999999', 'fecha_nac' => '2000-01-01', 'ciudad' => 'Valladolid', 'genero' => 0, 'id_grupo' => 0, 'id_user' => $user->id]);
        AlumnoExpediente::create(['id_alumno' => $student->id, 'version' => 1, 'estatus' => 'aprobado', 'respuestas_json' => []]);
        $home = $this->actingAs($user)->get('/site/index')->assertOk()->assertSee('Tu expediente está enviado')->assertDontSee('Pendiente de completar')->assertSee('data-student-carousel', false);
        $response = $this->get('/expediente/index')->assertOk()->assertSee('data-section-wizard', false)->assertSee('data-step-next', false)->assertSee('Las respuestas se guardan al enviar');
        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
        $this->assertSame(1, $dom->getElementsByTagName('h1')->length);
    }

    public function test_tutor_summary_includes_only_assigned_groups_and_rejects_foreign_session_selection(): void
    {
        $user = $this->user('tutor');
        $tutor = Tutor::create(['nombre' => 'Elena', 'apellido' => 'Prueba', 'correo' => 'elena@example.com', 'telefono' => '9999999999', 'genero' => 1, 'id_user' => $user->id]);
        $own = GrupoMaster::create(['id_tutor' => $tutor->id, 'id_periodo' => 1, 'id_carrera' => 1, 'id_semestre' => 1, 'id_grupoLetra' => 1]);
        $other = GrupoMaster::create(['id_tutor' => 999, 'id_periodo' => 1, 'id_carrera' => 1, 'id_semestre' => 1, 'id_grupoLetra' => 1]);
        $response = $this->actingAs($user)->withSession(['id_grupo' => $other->id])->get('/site/index')->assertOk()->assertSee('Selecciona tu grupo de trabajo')->assertDontSee('data-student-carousel', false);
        $this->assertNull($response->viewData('grupoActual'));
        $this->assertSame([$own->id], $response->viewData('grupos')->pluck('id')->all());
    }

    public function test_psychology_home_has_working_role_specific_actions(): void
    {
        $response = $this->actingAs($this->user('psicologo'))->get('/site/index')->assertOk()->assertSee('Espacio de psicología')->assertSee('/expediente-pregunta/index', false)->assertDontSee('Ir a mi grupo');
        $this->actingAs($this->user('psicologa'))->get('/site/index')->assertOk()->assertDontSee('Seguimiento de asistencia');
    }
}
