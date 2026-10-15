<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expediente_pregunta', function (Blueprint $table): void {
            $table->unsignedInteger('seccion_orden')->default(100);
            $table->text('configuracion')->nullable();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('expediente_pregunta', function (Blueprint $table): void {
            $table->dropColumn(['seccion_orden', 'configuracion', 'deleted_at']);
        });
    }
};
