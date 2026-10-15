<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Semana extends Model
{
    public $timestamps = false;

    protected $table = 'semana';

    protected $fillable = ['num_semana', 'nombre', 'tipo_tutoria', 'tematica', 'objetivos', 'justificacion', 'estrategias_tutor', 'acciones', 'estrategias_tutorado', 'id_pat'];

    public function pat(): BelongsTo
    {
        return $this->belongsTo(Pat::class, 'id_pat');
    }

    public function semanasReales(): HasMany
    {
        return $this->hasMany(SemanaReal::class, 'id_semana');
    }
}
