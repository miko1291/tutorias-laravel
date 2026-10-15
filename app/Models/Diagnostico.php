<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Diagnostico extends Model
{
    public $timestamps = false;

    protected $table = 'diagnostico';

    protected $fillable = ['id_alumno', 'motivo', 'asignaturas', 'otro', 'especifique'];

    protected $casts = ['id_alumno' => 'integer', 'motivo' => 'integer'];

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class, 'id_alumno');
    }
}
