<?php

namespace App\Services;

use App\Models\ExpedientePregunta;
use Illuminate\Support\Facades\DB;

class ExpedienteCatalogoService
{
    public static function prepararMunicipios(): void
    {
        DB::transaction(function (): void {
            $pregunta = ExpedientePregunta::where('clave', 'campo_012')->lockForUpdate()->first();
            if (! $pregunta || array_key_exists('municipios_por_estado', $pregunta->configuracion ?? [])) {
                return;
            }

            $datos = json_decode(file_get_contents(resource_path('municipios-region.json')), true, flags: JSON_THROW_ON_ERROR);
            $configuracion = $pregunta->configuracion ?? [];
            $configuracion['municipios_por_estado'] = $datos['estados'];
            $configuracion['estado_clave'] = 'campo_010';
            $opciones = array_values(array_unique([
                ...array_merge(...array_values($datos['estados'])),
                ...$pregunta->opciones,
                'Otro',
            ]));
            $pregunta->update([
                'tipo' => 'select',
                'configuracion' => $configuracion,
                'opciones_json' => json_encode($opciones, JSON_UNESCAPED_UNICODE),
            ]);
        });
    }
}
