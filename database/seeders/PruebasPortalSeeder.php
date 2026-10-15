<?php

namespace Database\Seeders;

use App\Models\Alumno;
use App\Models\AlumnoExpediente;
use App\Models\Asistencia;
use App\Models\Carreras;
use App\Models\Diagnostico;
use App\Models\Estatus;
use App\Models\ExpedientePregunta;
use App\Models\GrupoLetra;
use App\Models\GrupoMaster;
use App\Models\Notification;
use App\Models\Semestre;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class PruebasPortalSeeder extends Seeder
{
    public const PASSWORD = 'PruebasITSVA2026!';

    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Los datos ficticios solo se pueden cargar en local o pruebas.');
        }

        DB::transaction(function (): void {
            $activo = Estatus::firstOrCreate(['nombre' => 'Activo']);
            $cicloId = DB::table('ciclo_escolar')->where('nombre', 'Pruebas portal 2026')->value('id')
                ?? DB::table('ciclo_escolar')->insertGetId(['nombre' => 'Pruebas portal 2026', 'fecha_inicial' => '2026-01-01', 'fecha_final' => '2026-12-31', 'id_estatus' => $activo->id, 'created_at' => now(), 'updated_at' => now()]);
            $periodoId = DB::table('periodo_escolar')->where('nombre', 'Pruebas agosto-diciembre 2026')->value('id')
                ?? DB::table('periodo_escolar')->insertGetId(['nombre' => 'Pruebas agosto-diciembre 2026', 'id_ciclo' => $cicloId, 'id_estatus' => $activo->id, 'letra_periodo' => 'B', 'date_start' => '2026-08-01', 'date_end' => '2026-12-31', 'created_at' => now(), 'updated_at' => now()]);

            $admin = $this->account('prueba.admin', 'admin');
            $this->notice($admin, 'Bienvenida al entorno de pruebas', 'Estos registros son ficticios. Revisa los grupos de Pruebas portal 2026 y los expedientes pendientes.', '/admin/index');

            $tutores = [];
            foreach ([['prueba.tutor', 'María', 'Gómez Prueba', 1], ['prueba.tutor2', 'Daniel', 'Pérez Prueba', 0]] as [$username, $nombre, $apellido, $genero]) {
                $user = $this->account($username, 'tutor');
                $tutores[] = Tutor::firstOrCreate(['id_user' => $user->id], ['nombre' => $nombre, 'apellido' => $apellido, 'correo' => $user->email, 'telefono' => '0000000000', 'genero' => $genero]);
                $this->notice($user, 'Seguimiento de grupos de prueba', 'Consulta tus alumnos y distingue expedientes enviados, borradores y estudiantes sin expediente. Este aviso es ficticio.', '/grupo-master/pendientes');
            }

            $grupos = [];
            foreach ([['Ingeniería en Sistemas Computacionales', 1, 'A', 0], ['Ingeniería en Sistemas Computacionales', 3, 'B', 0], ['Ingeniería Industrial', 5, 'A', 1]] as [$carrera, $numero, $letra, $tutorIndex]) {
                $carrera = Carreras::firstOrCreate(['nombre' => $carrera]);
                $semestre = Semestre::firstOrCreate(['num_semestre' => $numero], ['nombre' => 'Semestre '.$numero]);
                $letra = GrupoLetra::firstOrCreate(['letra_key' => $letra]);
                $grupos[] = GrupoMaster::firstOrCreate(['id_periodo' => $periodoId, 'id_carrera' => $carrera->id, 'id_semestre' => $semestre->id, 'id_grupoLetra' => $letra->id], ['id_tutor' => $tutores[$tutorIndex]->id]);
            }

            $nombres = [['Ana', 'López', 1], ['Luis', 'Martínez', 0], ['Sofía', 'Hernández', 1], ['Diego', 'Torres', 0], ['Valeria', 'Sánchez', 1], ['Mateo', 'Ramírez', 0], ['Camila', 'Díaz', 1], ['Emilio', 'García', 0], ['Fernanda', 'Castillo', 1], ['Jorge', 'Mendoza', 0], ['Renata', 'Vargas', 1], ['Andrés', 'Ruiz', 0]];
            foreach ($nombres as $index => [$nombre, $apellido, $genero]) {
                $numero = $index + 1;
                $username = $numero === 1 ? 'prueba.alumno' : 'prueba.alumno'.$numero;
                $user = $this->account($username, 'alumno');
                $grupo = $grupos[intdiv($index, 4)];
                $matricula = sprintf('PRUEBA-%03d', $numero);
                $student = Alumno::firstOrCreate(['id_user' => $user->id], ['nombres' => $nombre, 'apellidop' => $apellido, 'apellidom' => 'Prueba', 'matricula' => $matricula, 'correo' => $user->email, 'telefono' => '0000000000', 'fecha_nac' => '2004-05-14', 'ciudad' => 'Valladolid', 'genero' => $genero, 'id_grupo' => $grupo->id]);
                $situacion = $index % 4;
                if ($situacion < 3) {
                    AlumnoExpediente::firstOrCreate(['id_alumno' => $student->id], ['version' => 1, 'estatus' => $situacion === 2 ? 'borrador' : ($situacion === 1 ? 'aprobado' : 'enviado'), 'observaciones' => 'Expediente ficticio para probar el portal.', 'respuestas_json' => $this->answers($student, $situacion === 2)]);
                }
                foreach (['2026-10-05', '2026-10-12'] as $date) {
                    Asistencia::firstOrCreate(['id_alumno' => $student->id, 'id_grupo' => $grupo->id, 'fecha' => $date], ['estado' => ($numero + (int) substr($date, -2)) % 4 !== 0]);
                }
                if ($situacion === 2) {
                    Diagnostico::firstOrCreate(['id_alumno' => $student->id], ['motivo' => 1, 'asignaturas' => 'Matemáticas', 'otro' => 'Ejemplo ficticio', 'especifique' => 'Practicar la organización del tiempo y las técnicas de estudio. Registro de prueba.']);
                }
                $this->notice($user, 'Bienvenida a las pruebas de tutoría', 'Esta cuenta contiene información ficticia. Puedes consultar tu perfil, revisar tu expediente y probar las notificaciones.', '/expediente/perfil');
                $this->notice($user, 'Revisión del expediente de prueba', $situacion < 2 ? 'Tu expediente de ejemplo ya está enviado. Puedes consultar la ficha.' : 'Tu expediente de ejemplo está pendiente. Completa las secciones y envíalo para probar el flujo.', $situacion < 2 ? '/expediente/ficha' : '/expediente/index');
            }
        });

        $this->command?->info('Datos de prueba listos: 1 administrador, 2 tutores, 12 alumnos, 3 grupos y ejemplos de expedientes, asistencias, diagnósticos y avisos. No se enviaron correos.');
    }

    private function account(string $username, string $role): User
    {
        $email = $username.'@example.test';
        $user = User::where('username', $username)->first();
        if (! $user) {
            $id = DB::table('user')->insertGetId(['username' => $username, 'email' => $email, 'password_hash' => Hash::make(self::PASSWORD), 'auth_key' => Str::random(32), 'status' => User::STATUS_ACTIVE, 'created_at' => time(), 'updated_at' => time()]);
            $user = User::findOrFail($id);
        }
        if ($user->email !== $email) {
            throw new RuntimeException('El usuario '.$username.' ya existe con otro correo. No se modificó.');
        }
        DB::table('auth_assignment')->insertOrIgnore(['item_name' => $role, 'user_id' => (string) $user->id, 'created_at' => time()]);

        return $user;
    }

    private function notice(User $user, string $title, string $message, string $url): void
    {
        Notification::firstOrCreate(['user_id' => $user->id, 'title' => $title], ['message' => $message, 'url' => $url, 'type' => 'info', 'created_at' => time()]);
    }

    private function answers(Alumno $student, bool $draft): array
    {
        $answers = [];
        foreach (ExpedientePregunta::activas() as $question) {
            $choices = $question->opciones;
            $choice = in_array('No', $choices, true) ? 'No' : ($choices[0] ?? '');
            $answers[$question->clave] = match ($question->tipo) {
                'email' => $student->correo,
                'tel' => $student->telefono,
                'date' => '2026-10-12',
                'number' => (string) ($question->configuracion['min'] ?? 2),
                'select', 'radio' => $choice,
                'checkbox' => $choice !== '' ? [$choice] : [],
                default => 'Información ficticia de prueba',
            };
        }
        $answers = array_replace($answers, ['campo_002' => '2026-10-12', 'campo_004' => $student->correo, 'campo_006' => $student->genero === 1 ? 'Mujer' : 'Hombre', 'fecha_nacimiento' => '2004-05-14', 'campo_012' => 'Valladolid', 'campo_013' => 'Valladolid']);

        return $draft ? array_intersect_key($answers, array_flip(['campo_002', 'campo_004', 'campo_006', 'fecha_nacimiento'])) : $answers;
    }
}
