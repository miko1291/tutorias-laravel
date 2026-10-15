<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Carreras extends Model
{
    protected $table = 'carreras';

    public $timestamps = false;

    protected $fillable = ['nombre'];

    public function grupoMasters(): HasMany
    {
        return $this->hasMany(GrupoMaster::class, 'id_carrera');
    }
}
