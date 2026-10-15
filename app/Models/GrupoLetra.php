<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrupoLetra extends Model
{
    protected $table = 'grupo_letra';

    public $timestamps = false;

    protected $fillable = ['letra_key'];

    public function grupoMasters(): HasMany
    {
        return $this->hasMany(GrupoMaster::class, 'id_grupoLetra');
    }
}
