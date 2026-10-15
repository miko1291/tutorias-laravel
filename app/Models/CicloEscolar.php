<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CicloEscolar extends Model
{
    protected $table = 'ciclo_escolar';

    public $timestamps = false;

    protected $fillable = ['nombre', 'fecha_inicial', 'fecha_final', 'id_estatus'];

    protected $casts = ['id_estatus' => 'integer', 'fecha_inicial' => 'date', 'fecha_final' => 'date'];

    public function periodos(): HasMany
    {
        return $this->hasMany(PeriodoEscolar::class, 'id_ciclo');
    }
}
