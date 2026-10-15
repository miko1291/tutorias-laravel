<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asistencia extends Model
{
    public $timestamps = false;

    protected $table = 'asistencia';

    protected $fillable = ['id_alumno', 'id_grupo', 'fecha', 'estado'];

    protected $casts = ['id_alumno' => 'integer', 'id_grupo' => 'integer', 'fecha' => 'date', 'estado' => 'boolean'];

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class, 'id_alumno');
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(GrupoMaster::class, 'id_grupo');
    }
}
