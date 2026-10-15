<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumno_expediente', function (Blueprint $table): void {
            $table->index(['id_alumno', 'id'], 'idx-expediente-alumno-latest');
        });

        Schema::table('asistencia', function (Blueprint $table): void {
            $table->index(['id_grupo', 'fecha'], 'idx-asistencia-grupo-fecha');
        });
    }

    public function down(): void
    {
        Schema::table('alumno_expediente', function (Blueprint $table): void {
            $table->dropIndex('idx-expediente-alumno-latest');
        });

        Schema::table('asistencia', function (Blueprint $table): void {
            $table->dropIndex('idx-asistencia-grupo-fecha');
        });
    }
};
