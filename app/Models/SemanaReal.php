<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SemanaReal extends Model
{
    public $timestamps = false;

    protected $table = 'semana_real';

    protected $fillable = ['id_grupomaster', 'id_semana', 'semana_atendida', 'alumnos_atendidos', 'alumnos_faltantes', 'total_alumnos', 'atendidos_hombres', 'atendidos_mujeres', 'total_gatendidos', 'evidencias', 'observaciones', 'alumnos'];

    protected $casts = ['id_grupomaster' => 'integer', 'id_semana' => 'integer'];

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(GrupoMaster::class, 'id_grupomaster');
    }

    public function semana(): BelongsTo
    {
        return $this->belongsTo(Semana::class, 'id_semana');
    }
}
