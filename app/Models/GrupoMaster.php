<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrupoMaster extends Model
{
    public $timestamps = false;

    protected $table = 'grupo_master';

    protected $fillable = [
        'id_periodo',
        'id_carrera',
        'id_semestre',
        'id_grupoLetra',
        'id_tutor',
    ];

    protected $casts = [
        'id_periodo' => 'integer',
        'id_carrera' => 'integer',
        'id_semestre' => 'integer',
        'id_grupoLetra' => 'integer',
        'id_tutor' => 'integer',
    ];

    public function alumnos(): HasMany
    {
        return $this->hasMany(Alumno::class, 'id_grupo');
    }

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carreras::class, 'id_carrera');
    }

    public function grupoLetra(): BelongsTo
    {
        return $this->belongsTo(GrupoLetra::class, 'id_grupoLetra');
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(PeriodoEscolar::class, 'id_periodo');
    }

    public function semestre(): BelongsTo
    {
        return $this->belongsTo(Semestre::class, 'id_semestre');
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class, 'id_tutor');
    }

    public function getNombreAttribute(): string
    {
        $partes = array_filter([
            $this->carrera?->nombre,
            $this->semestre?->nombre,
            $this->grupoLetra ? 'Grupo '.$this->grupoLetra->letra_key : null,
        ]);

        return implode(' · ', $partes);
    }
}
