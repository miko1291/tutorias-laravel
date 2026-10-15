<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Criterio extends Model
{
    public $timestamps = false;

    protected $table = 'criterios';

    protected $fillable = ['nombre'];

    public function evaluaciones(): HasMany
    {
        return $this->hasMany(Evaluacion::class, 'id_criterio');
    }
}
