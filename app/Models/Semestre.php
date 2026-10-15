<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Semestre extends Model
{
    protected $table = 'semestre';

    public $timestamps = false;

    protected $fillable = ['nombre', 'num_semestre'];

    protected $casts = ['num_semestre' => 'integer'];

    public function grupoMasters(): HasMany
    {
        return $this->hasMany(GrupoMaster::class, 'id_semestre');
    }
}
