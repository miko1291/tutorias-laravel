<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlumnoExpediente extends Model
{
    public const ESTATUS_BORRADOR = 'borrador';

    public const ESTATUS_ENVIADO = 'enviado';

    protected $table = 'alumno_expediente';

    protected $fillable = [
        'id_alumno',
        'version',
        'respuestas_json',
        'estatus',
        'observaciones',
        'revisado_por',
        'revisado_at',
    ];

    protected $attributes = [
        'version' => 1,
        'estatus' => self::ESTATUS_BORRADOR,
    ];

    protected $casts = [
        'id_alumno' => 'integer',
        'version' => 'integer',
        'revisado_por' => 'integer',
        'respuestas_json' => 'array',
    ];

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class, 'id_alumno');
    }

    public function revisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisado_por');
    }

    public function getRespuestas(): array
    {
        $decoded = is_array($this->respuestas_json)
            ? $this->respuestas_json
            : json_decode((string) $this->respuestas_json, true);

        return is_array($decoded) ? $decoded : [];
    }
}
