<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user')) {
            Schema::create('user', function (Blueprint $table): void {
                $table->smallIncrements('id');
                $table->string('username', 255)->unique();
                $table->string('auth_key', 32);
                $table->string('password_hash', 255);
                $table->string('password_reset_token', 255)->nullable()->unique();
                $table->string('email', 255)->unique();
                $table->smallInteger('status')->default(10);
                $table->unsignedInteger('created_at');
                $table->unsignedInteger('updated_at');
                $table->string('verification_token', 255)->nullable();
            });
        }

        if (! Schema::hasTable('auth_assignment')) {
            Schema::create('auth_assignment', function (Blueprint $table): void {
                $table->string('item_name', 64);
                $table->string('user_id', 64);
                $table->unsignedInteger('created_at')->nullable();
                $table->primary(['item_name', 'user_id']);
                $table->index('user_id', 'idx-auth_assignment-user_id');
            });
        }

        if (! Schema::hasTable('auth_item')) {
            Schema::create('auth_item', function (Blueprint $table): void {
                $table->string('name', 64)->primary();
                $table->smallInteger('type');
                $table->text('description')->nullable();
                $table->string('rule_name', 64)->nullable();
                $table->binary('data')->nullable();
                $table->unsignedInteger('created_at')->nullable();
                $table->unsignedInteger('updated_at')->nullable();
                $table->index('rule_name');
                $table->index('type', 'idx-auth_item-type');
            });
        }

        if (! Schema::hasTable('auth_item_child')) {
            Schema::create('auth_item_child', function (Blueprint $table): void {
                $table->string('parent', 64);
                $table->string('child', 64);
                $table->primary(['parent', 'child']);
                $table->index('child');
            });
        }

        if (! Schema::hasTable('auth_rule')) {
            Schema::create('auth_rule', function (Blueprint $table): void {
                $table->string('name', 64)->primary();
                $table->binary('data')->nullable();
                $table->unsignedInteger('created_at')->nullable();
                $table->unsignedInteger('updated_at')->nullable();
            });
        }

        if (! Schema::hasTable('carreras')) {
            Schema::create('carreras', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('nombre', 150);
            });
        }

        if (! Schema::hasTable('estatus')) {
            Schema::create('estatus', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('nombre', 50);
            });
        }

        if (! Schema::hasTable('grupo_letra')) {
            Schema::create('grupo_letra', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('letra_key', 2);
            });
        }

        if (! Schema::hasTable('semestre')) {
            Schema::create('semestre', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('nombre', 50);
                $table->integer('num_semestre');
            });
        }

        if (! Schema::hasTable('ciclo_escolar')) {
            Schema::create('ciclo_escolar', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('nombre', 100);
                $table->dateTime('created_at');
                $table->dateTime('updated_at');
                $table->date('fecha_inicial');
                $table->date('fecha_final');
                $table->unsignedInteger('id_estatus');
                $table->index('id_estatus');
            });
        }

        if (! Schema::hasTable('periodo_escolar')) {
            Schema::create('periodo_escolar', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('nombre', 50);
                $table->unsignedInteger('id_estatus');
                $table->string('letra_periodo', 2);
                $table->date('date_start');
                $table->date('date_end');
                $table->unsignedInteger('id_ciclo');
                $table->dateTime('created_at');
                $table->dateTime('updated_at');
                $table->index('id_ciclo');
                $table->index('id_estatus');
            });
        }

        if (! Schema::hasTable('tutor')) {
            Schema::create('tutor', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('nombre', 100);
                $table->string('apellido', 45);
                $table->string('correo', 100);
                $table->string('telefono', 20);
                $table->integer('genero');
                $table->unsignedSmallInteger('id_user');
                $table->index('id_user');
            });
        }

        if (! Schema::hasTable('grupo_master')) {
            Schema::create('grupo_master', function (Blueprint $table): void {
                $table->increments('id');
                $table->unsignedInteger('id_periodo');
                $table->unsignedInteger('id_carrera');
                $table->unsignedInteger('id_semestre');
                $table->unsignedInteger('id_grupoLetra');
                $table->unsignedInteger('id_tutor')->nullable();
                $table->index('id_periodo');
                $table->index('id_carrera');
                $table->index('id_semestre');
                $table->index('id_grupoLetra');
                $table->index('id_tutor');
            });
        }

        if (! Schema::hasTable('alumno')) {
            Schema::create('alumno', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('nombres', 100);
                $table->string('apellidop', 50);
                $table->string('apellidom', 50);
                $table->string('matricula', 50);
                $table->string('correo', 50);
                $table->string('telefono', 20);
                $table->date('fecha_nac');
                $table->string('ciudad', 50);
                $table->integer('genero');
                $table->dateTime('created_at');
                $table->dateTime('updated_at');
                $table->unsignedInteger('id_grupo');
                $table->unsignedSmallInteger('id_user')->nullable();
                $table->index('id_grupo', 'idx_grupoMaster');
                $table->index('id_user');
            });
        }

        if (! Schema::hasTable('alumno_expediente')) {
            Schema::create('alumno_expediente', function (Blueprint $table): void {
                $table->increments('id');
                $table->unsignedInteger('id_alumno');
                $table->unsignedInteger('version')->default(1);
                $table->text('respuestas_json')->nullable();
                $table->string('estatus', 20)->default('borrador');
                $table->text('observaciones')->nullable();
                $table->unsignedSmallInteger('revisado_por')->nullable();
                $table->dateTime('revisado_at')->nullable();
                $table->dateTime('created_at');
                $table->dateTime('updated_at');
                $table->index('id_alumno');
            });
        }

        if (! Schema::hasTable('expediente_pregunta')) {
            Schema::create('expediente_pregunta', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('clave', 90)->unique();
                $table->string('titulo', 255);
                $table->text('descripcion')->nullable();
                $table->string('tipo', 20)->default('text');
                $table->text('opciones_json')->nullable();
                $table->string('seccion', 120)->default('Preguntas adicionales');
                $table->boolean('requerido')->default(false);
                $table->boolean('activo')->default(true);
                $table->integer('orden')->default(100);
                $table->dateTime('created_at');
                $table->dateTime('updated_at');
                $table->index(['activo', 'orden'], 'idx-expediente_pregunta-activo-orden');
            });
        }

        if (! Schema::hasTable('notification')) {
            Schema::create('notification', function (Blueprint $table): void {
                $table->increments('id');
                $table->unsignedSmallInteger('user_id');
                $table->string('title', 255);
                $table->text('message');
                $table->string('type', 30)->default('info');
                $table->string('url', 255)->nullable();
                $table->unsignedInteger('read_at')->nullable();
                $table->unsignedInteger('email_sent_at')->nullable();
                $table->unsignedInteger('created_at');
                $table->index(['user_id', 'read_at'], 'idx-notification-user-read');
                $table->index('created_at', 'idx-notification-created');
            });
        }

    }

    public function down(): void
    {
        foreach ([
            'notification',
            'expediente_pregunta',
            'alumno_expediente',
            'alumno',
            'grupo_master',
            'tutor',
            'periodo_escolar',
            'ciclo_escolar',
            'semestre',
            'grupo_letra',
            'estatus',
            'carreras',
            'auth_rule',
            'auth_item_child',
            'auth_item',
            'auth_assignment',
            'user',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
