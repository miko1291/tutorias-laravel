<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Alumno extends Model
{
    public const GENEROS = [0 => 'MASCULINO', 1 => 'FEMENINO'];

    protected $table = 'alumno';

    protected $fillable = [
        'nombres',
        'apellidop',
        'apellidom',
        'matricula',
        'correo',
        'telefono',
        'fecha_nac',
        'ciudad',
        'genero',
        'id_grupo',
        'id_user',
    ];

    protected $casts = [
        'genero' => 'integer',
        'id_grupo' => 'integer',
        'id_user' => 'integer',
    ];

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(GrupoMaster::class, 'id_grupo');
    }

    public function evaluaciones(): HasMany
    {
        return $this->hasMany(Evaluacion::class, 'id_alumno');
    }

    public function expedientes(): HasMany
    {
        return $this->hasMany(AlumnoExpediente::class, 'id_alumno');
    }

    public function latestExpediente(): HasOne
    {
        return $this->hasOne(AlumnoExpediente::class, 'id_alumno')->latestOfMany('id');
    }

    public function scopePendientesExpediente(Builder $query): Builder
    {
        return $query->whereDoesntHave('latestExpediente', function (Builder $expedientes): void {
            $expedientes->whereIn('estatus', ['enviado', 'aprobado']);
        });
    }

    public function getGeneroTextoAttribute(): string
    {
        return self::GENEROS[$this->genero] ?? (string) $this->genero;
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombres} {$this->apellidop} {$this->apellidom}");
    }
}
