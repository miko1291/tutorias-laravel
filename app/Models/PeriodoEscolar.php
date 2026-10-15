<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeriodoEscolar extends Model
{
    protected $table = 'periodo_escolar';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'id_ciclo',
        'id_estatus',
        'letra_periodo',
        'date_start',
        'date_end',
    ];

    protected $casts = [
        'id_ciclo' => 'integer',
        'id_estatus' => 'integer',
        'date_start' => 'date',
        'date_end' => 'date',
    ];

    public function ciclo(): BelongsTo
    {
        return $this->belongsTo(CicloEscolar::class, 'id_ciclo');
    }

    public function estatus(): BelongsTo
    {
        return $this->belongsTo(Estatus::class, 'id_estatus');
    }

    public function grupoMasters(): HasMany
    {
        return $this->hasMany(GrupoMaster::class, 'id_periodo');
    }
}
