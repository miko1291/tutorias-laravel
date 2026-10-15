<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencia', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('id_alumno');
            $table->unsignedInteger('id_grupo');
            $table->date('fecha');
            $table->boolean('estado');
            $table->index(['id_alumno', 'fecha']);
            $table->index('id_grupo');
        });

        Schema::create('criterios', function (Blueprint $table): void {
            $table->increments('id');
            $table->text('nombre');
        });

        Schema::create('diagnostico', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('id_alumno');
            $table->integer('motivo');
            $table->text('asignaturas');
            $table->text('otro');
            $table->text('especifique');
            $table->index('id_alumno', 'idx-diagnostico-alumno');
        });

        Schema::create('evaluacion', function (Blueprint $table): void {
            $table->increments('id');
            $table->integer('calificacion');
            $table->unsignedInteger('id_alumno');
            $table->unsignedInteger('id_criterio');
            $table->index('id_alumno', 'idx-evaluacion-alumno');
            $table->index('id_criterio', 'idx-evaluacion-criterio');
        });

        Schema::create('canalizacion', function (Blueprint $table): void {
            $table->increments('id');
            $table->integer('estatus');
            $table->text('asunto');
            $table->text('cuerpo');
            $table->unsignedInteger('id_alumno');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->index('id_alumno', 'idx-canalizacion-alumno');
        });

        Schema::create('pat', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('id_semestre');
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->integer('estatus');
            $table->index('id_semestre', 'idx-pat-semestre');
        });

        Schema::create('semana', function (Blueprint $table): void {
            $table->increments('id');
            $table->integer('num_semana');
            $table->string('nombre', 100);
            $table->integer('tipo_tutoria');
            $table->text('tematica');
            $table->text('objetivos');
            $table->text('justificacion');
            $table->text('estrategias_tutor');
            $table->text('acciones');
            $table->text('estrategias_tutorado')->nullable();
            $table->unsignedInteger('id_pat');
            $table->index('id_pat', 'idx-semana-pat');
        });

        Schema::create('semana_real', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('id_grupomaster');
            $table->unsignedInteger('id_semana');
            $table->integer('semana_atendida');
            $table->integer('alumnos_atendidos');
            $table->integer('alumnos_faltantes');
            $table->integer('total_alumnos');
            $table->integer('atendidos_hombres');
            $table->integer('atendidos_mujeres');
            $table->integer('total_gatendidos');
            $table->longText('evidencias');
            $table->text('observaciones');
            $table->text('alumnos')->nullable();
            $table->index('id_grupomaster', 'idx-semana-real-grupo');
            $table->index('id_semana', 'idx-semana-real-semana');
        });

        Schema::create('performance', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('id_grupo');
            $table->integer('eDesempeño');
            $table->integer('bDesempeño');
            $table->integer('arDesempeño');
            $table->text('comentarios')->nullable();
            $table->index('id_grupo', 'idx-performance-grupo');
        });
    }

    public function down(): void
    {
        foreach (['performance', 'semana_real', 'semana', 'pat', 'canalizacion', 'evaluacion', 'diagnostico', 'criterios', 'asistencia'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
