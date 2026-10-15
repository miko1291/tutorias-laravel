<?php

namespace Database\Seeders;

use App\Models\Alumno;
use App\Models\AlumnoExpediente;
use App\Models\Carreras;
use App\Models\CicloEscolar;
use App\Models\Estatus;
use App\Models\GrupoLetra;
use App\Models\GrupoMaster;
use App\Models\PeriodoEscolar;
use App\Models\Semestre;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $activo = Estatus::firstOrCreate(['nombre' => 'Activo']);
            $carrera = Carreras::firstOrCreate(['nombre' => 'Ingeniería en Sistemas Computacionales']);
            $semestre = Semestre::firstOrCreate(['nombre' => 'Sexto semestre', 'num_semestre' => 6]);
            $letra = GrupoLetra::firstOrCreate(['letra_key' => 'A']);
            $ciclo = CicloEscolar::firstOrCreate(
                ['nombre' => 'Ciclo demo 2026'],
                [
                    'fecha_inicial' => '2026-01-01',
                    'fecha_final' => '2026-12-31',
                    'id_estatus' => $activo->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            $periodo = PeriodoEscolar::firstOrCreate(
                ['nombre' => 'Periodo demo enero-junio 2026'],
                [
                    'id_ciclo' => $ciclo->id,
                    'id_estatus' => $activo->id,
                    'letra_periodo' => 'A',
                    'date_start' => '2026-01-01',
                    'date_end' => '2026-06-30',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $tutorUser = $this->user('demo.tutor', 'demo.tutor@example.test', 'TutorDemo123!');
            $this->assignRole($tutorUser, 'tutor');
            $tutor = Tutor::updateOrCreate(
                ['id_user' => $tutorUser->id],
                [
                    'nombre' => 'María',
                    'apellido' => 'Gómez Demo',
                    'correo' => $tutorUser->email,
                    'telefono' => '9990001000',
                    'genero' => 1,
                ]
            );

            $grupo = GrupoMaster::firstOrCreate([
                'id_periodo' => $periodo->id,
                'id_carrera' => $carrera->id,
                'id_semestre' => $semestre->id,
                'id_grupoLetra' => $letra->id,
            ], ['id_tutor' => $tutor->id]);
            if ($grupo->id_tutor !== $tutor->id) {
                $grupo->update(['id_tutor' => $tutor->id]);
            }

            $studentUser = $this->user('demo.alumno', 'demo.alumno@example.test', 'AlumnoDemo123!');
            $this->assignRole($studentUser, 'alumno');
            $student = Alumno::updateOrCreate(
                ['id_user' => $studentUser->id],
                [
                    'nombres' => 'Carlos',
                    'apellidop' => 'Ramírez',
                    'apellidom' => 'Demo',
                    'matricula' => 'DEMO-001',
                    'correo' => $studentUser->email,
                    'telefono' => '9990002000',
                    'fecha_nac' => '2002-05-14',
                    'ciudad' => 'Valladolid',
                    'genero' => 0,
                    'id_grupo' => $grupo->id,
                ]
            );
            AlumnoExpediente::updateOrCreate(
                ['id_alumno' => $student->id],
                [
                    'version' => 1,
                    'estatus' => AlumnoExpediente::ESTATUS_ENVIADO,
                    'respuestas_json' => [
                        'fecha_nacimiento' => '2002-05-14',
                        'ciudad' => 'Valladolid',
                        'idiomas' => ['Español'],
                        'habitos_estudio' => ['Lectura', 'Prácticas'],
                    ],
                ]
            );

            foreach ([
                ['Ana', 'López', 'Demo', 'DEMO-002', 'ana.demo@example.test'],
                ['Luis', 'Martínez', 'Demo', 'DEMO-003', 'luis.demo@example.test'],
            ] as [$nombre, $apellidoPaterno, $apellidoMaterno, $matricula, $correo]) {
                Alumno::updateOrCreate(
                    ['matricula' => $matricula],
                    [
                        'nombres' => $nombre,
                        'apellidop' => $apellidoPaterno,
                        'apellidom' => $apellidoMaterno,
                        'correo' => $correo,
                        'telefono' => '9990003000',
                        'fecha_nac' => '2001-08-20',
                        'ciudad' => 'Valladolid',
                        'genero' => 1,
                        'id_grupo' => $grupo->id,
                    ]
                );
            }
        });
    }

    private function user(string $username, string $email, string $password): User
    {
        $timestamp = now()->timestamp;
        $attributes = [
            'auth_key' => str_repeat('d', 32),
            'password_hash' => Hash::make($password),
            'email' => $email,
            'status' => User::STATUS_ACTIVE,
            'updated_at' => $timestamp,
        ];
        $id = DB::table('user')->where('username', $username)->value('id');

        if ($id) {
            DB::table('user')->where('id', $id)->update($attributes);
        } else {
            $id = DB::table('user')->insertGetId($attributes + [
                'username' => $username,
                'created_at' => $timestamp,
            ]);
        }

        return User::findOrFail($id);
    }

    private function assignRole(User $user, string $role): void
    {
        DB::table('auth_assignment')->where('user_id', (string) $user->id)->delete();
        DB::table('auth_assignment')->insert([
            'item_name' => $role,
            'user_id' => (string) $user->id,
            'created_at' => now()->timestamp,
        ]);
    }
}
