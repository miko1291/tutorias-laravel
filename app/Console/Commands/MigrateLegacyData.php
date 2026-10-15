<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateLegacyData extends Command
{
    protected $signature = 'tutorias:migrate-legacy
        {--dry-run : Mostrar registros que se migrarían sin escribir}
        {--table=* : Migrar únicamente las tablas indicadas}';

    protected $description = 'Copia datos Yii2 a Laravel sin sobrescribir registros existentes';

    private const TABLES = [
        'user',
        'auth_assignment',
        'auth_item',
        'auth_item_child',
        'auth_rule',
        'carreras',
        'estatus',
        'grupo_letra',
        'semestre',
        'ciclo_escolar',
        'periodo_escolar',
        'tutor',
        'grupo_master',
        'alumno',
        'alumno_expediente',
        'expediente_pregunta',
        'notification',
        'asistencia',
        'criterios',
        'diagnostico',
        'evaluacion',
        'canalizacion',
        'pat',
        'semana',
        'semana_real',
        'performance',
    ];

    public function handle(): int
    {
        $source = DB::connection('legacy');
        $selected = $this->option('table');
        $tables = $selected ? array_values(array_intersect(self::TABLES, $selected)) : self::TABLES;

        if ($tables === []) {
            $this->error('No se indicó una tabla válida.');

            return self::INVALID;
        }

        $this->info('Origen: '.config('database.connections.legacy.database'));
        $this->info('Destino: '.config('database.default').'.'.config('database.connections.'.config('database.default').'.database'));

        DB::transaction(function () use ($source, $tables): void {
            foreach ($tables as $table) {
                if (! Schema::connection('legacy')->hasTable($table)) {
                    $this->warn("Omitida {$table}: no existe en el origen.");

                    continue;
                }

                if (! Schema::hasTable($table)) {
                    $this->warn("Omitida {$table}: no existe en Laravel.");

                    continue;
                }

                $this->copyTable($source, $table, (bool) $this->option('dry-run'));
            }
        });

        return self::SUCCESS;
    }

    private function copyTable(Connection $source, string $table, bool $dryRun): void
    {
        $sourceColumns = Schema::connection('legacy')->getColumnListing($table);
        $targetColumns = Schema::getColumnListing($table);
        $columns = array_values(array_intersect($sourceColumns, $targetColumns));

        if ($columns === []) {
            $this->warn("Omitida {$table}: no hay columnas compatibles.");

            return;
        }

        $rows = $source->table($table)->select($columns)->orderBy($columns[0])->get();
        $this->line(sprintf('%s: %d registro(s)', $table, $rows->count()));

        if ($dryRun || $rows->isEmpty()) {
            return;
        }

        $payload = $rows->map(static fn ($row): array => (array) $row)->all();
        foreach (array_chunk($payload, 500) as $chunk) {
            DB::table($table)->insertOrIgnore($chunk);
        }
    }
}
