<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Canalizacion extends Model
{
    protected $table = 'canalizacion';

    protected $fillable = ['estatus', 'asunto', 'cuerpo', 'id_alumno'];

    protected $casts = ['estatus' => 'integer', 'id_alumno' => 'integer'];

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class, 'id_alumno');
    }
}
