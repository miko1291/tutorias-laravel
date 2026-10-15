<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evaluacion extends Model
{
    public $timestamps = false;

    protected $table = 'evaluacion';

    protected $fillable = ['calificacion', 'id_alumno', 'id_criterio'];

    protected $casts = ['calificacion' => 'integer', 'id_alumno' => 'integer', 'id_criterio' => 'integer'];

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class, 'id_alumno');
    }

    public function criterio(): BelongsTo
    {
        return $this->belongsTo(Criterio::class, 'id_criterio');
    }
}
