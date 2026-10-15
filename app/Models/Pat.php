<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pat extends Model
{
    public $timestamps = false;

    protected $table = 'pat';

    protected $fillable = ['id_semestre', 'nombre', 'descripcion', 'estatus'];

    protected $casts = ['id_semestre' => 'integer', 'estatus' => 'integer'];

    public function semestre(): BelongsTo
    {
        return $this->belongsTo(Semestre::class, 'id_semestre');
    }

    public function semanas(): HasMany
    {
        return $this->hasMany(Semana::class, 'id_pat');
    }
}
