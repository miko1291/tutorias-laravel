<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class ExpedientePregunta extends Model
{
    use SoftDeletes;

    protected $table = 'expediente_pregunta';

    protected $fillable = [
        'clave',
        'titulo',
        'descripcion',
        'tipo',
        'opciones_json',
        'seccion',
        'requerido',
        'activo',
        'orden',
        'seccion_orden',
        'configuracion',
    ];

    protected $casts = [
        'requerido' => 'integer',
        'activo' => 'integer',
        'orden' => 'integer',
        'seccion_orden' => 'integer',
        'configuracion' => 'array',
    ];

    public function getOpcionesAttribute(): array
    {
        $options = json_decode((string) $this->opciones_json, true);

        return is_array($options) ? array_values(array_filter($options, 'is_string')) : [];
    }

    public static function activas()
    {
        return static::where('activo', 1)
            ->orderBy('seccion_orden')
            ->orderBy('seccion')
            ->orderBy('orden')
            ->orderBy('id')
            ->get();
    }

    public function opcionesPara(array $respuestas): array
    {
        $configuracion = $this->configuracion ?? [];
        if (! isset($configuracion['municipios_por_estado'])) {
            return $this->opciones;
        }
        $estado = $respuestas[$configuracion['estado_clave'] ?? 'campo_010'] ?? '';

        return array_values(array_unique([
            ...(is_string($estado) ? ($configuracion['municipios_por_estado'][$estado] ?? []) : []),
            'Otro',
        ]));
    }

    public function visibleCon(array $answers, Collection $questions, array $visited = []): bool
    {
        if (in_array($this->clave, $visited, true)) {
            return false;
        }
        $condition = $this->configuracion['condicion'] ?? null;
        if (! $condition) {
            return true;
        }
        $visited[] = $this->clave;
        foreach (array_filter([$condition['clave'], $condition['alternativa'] ?? null]) as $key) {
            $parent = $questions->get($key);
            $value = $answers[$key] ?? [];
            $values = is_array($value) ? $value : [$value];
            if ($parent && $parent->visibleCon($answers, $questions, $visited) && in_array($condition['valor'], array_map('strval', array_filter($values, 'is_scalar')), true)) {
                return true;
            }
        }

        return false;
    }
}
