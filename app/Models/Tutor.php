<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tutor extends Model
{
    public $timestamps = false;

    protected $table = 'tutor';

    protected $fillable = [
        'nombre',
        'apellido',
        'correo',
        'telefono',
        'genero',
        'id_user',
    ];

    protected $casts = [
        'genero' => 'integer',
        'id_user' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function grupoMasters(): HasMany
    {
        return $this->hasMany(GrupoMaster::class, 'id_tutor');
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombre} {$this->apellido}");
    }
}
