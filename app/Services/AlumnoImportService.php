<?php

namespace App\Services;

use App\Models\Alumno;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class AlumnoImportService
{
    public const COLUMNS = ['nombres', 'apellidop', 'apellidom', 'matricula', 'correo', 'telefono', 'fecha_nac', 'ciudad', 'genero'];

    public function import(string $path, int $groupId): int
    {
        $book = null;
        try {
            $type = IOFactory::identify($path, ['Xlsx', 'Xls', 'Csv']);
            $reader = IOFactory::createReader($type);
            $info = $reader->listWorksheetInfo($path)[0];
            if ($info['totalRows'] > 2001 || $info['totalColumns'] > 30) {
                throw ValidationException::withMessages(['archivo' => 'El archivo admite hasta 2,000 alumnos y 30 columnas.']);
            }
            $reader->setLoadSheetsOnly($info['worksheetName']);
            $reader->setReadFilter(new class implements IReadFilter
            {
                public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
                {
                    return $row <= 2001 && Coordinate::columnIndexFromString($columnAddress) <= 30;
                }
            });
            $book = $reader->load($path);
            $sheet = $book->getSheet(0);
            $headers = [];
            for ($column = 1; $column <= $info['totalColumns']; $column++) {
                $header = Str::snake(Str::ascii(trim((string) $sheet->getCell([$column, 1])->getValue())));
                if (in_array($header, self::COLUMNS, true)) {
                    if (isset($headers[$header])) {
                        throw ValidationException::withMessages(['archivo' => "La columna {$header} está repetida."]);
                    }
                    $headers[$header] = $column;
                }
            }
            $missing = array_diff(self::COLUMNS, array_keys($headers));
            if ($missing) {
                throw ValidationException::withMessages(['archivo' => 'Faltan columnas: '.implode(', ', $missing).'. Usa la plantilla.']);
            }

            $rows = [];
            $errors = [];
            $seen = [];
            for ($number = 2; $number <= $info['totalRows']; $number++) {
                $data = [];
                $hasFormula = false;
                foreach ($headers as $field => $column) {
                    $cell = $sheet->getCell([$column, $number]);
                    if ($cell->getDataType() === DataType::TYPE_FORMULA) {
                        $hasFormula = true;
                        $data[$field] = '';
                    } elseif ($field === 'fecha_nac' && is_numeric($cell->getValue()) && Date::isDateTime($cell)) {
                        $data[$field] = Date::excelToDateTimeObject((float) $cell->getValue())->format('Y-m-d');
                    } else {
                        $data[$field] = trim((string) $cell->getFormattedValue());
                    }
                }
                if (! $hasFormula && count(array_filter($data, fn ($value) => $value !== '')) === 0) {
                    continue;
                }
                $gender = Str::lower($data['genero']);
                $data['genero'] = match ($gender) {
                    'masculino', 'm', '0' => 0,
                    'femenino', 'f', '1' => 1,
                    default => $data['genero'],
                };
                $data['id_grupo'] = $groupId;
                $validator = Validator::make($data, [
                    'nombres' => ['required', 'string', 'max:100'],
                    'apellidop' => ['required', 'string', 'max:50'],
                    'apellidom' => ['required', 'string', 'max:50'],
                    'matricula' => ['required', 'string', 'max:50'],
                    'correo' => ['required', 'email', 'max:50'],
                    'telefono' => ['required', 'string', 'max:20'],
                    'fecha_nac' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
                    'ciudad' => ['required', 'string', 'max:50'],
                    'genero' => ['required', 'integer', 'in:0,1'],
                ], [
                    'required' => 'El campo :attribute es obligatorio.',
                    'max' => 'El campo :attribute admite hasta :max caracteres.',
                    'email' => 'El correo no es válido.',
                    'date_format' => 'La fecha debe usar AAAA-MM-DD o ser una fecha de Excel.',
                    'before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
                    'integer' => 'El género debe ser Masculino (0) o Femenino (1).',
                    'in' => 'El género debe ser Masculino (0) o Femenino (1).',
                ]);
                $messages = $validator->errors()->all();
                if ($hasFormula) {
                    $messages[] = 'Sustituye las fórmulas por valores antes de importar.';
                }
                $key = Str::lower($data['matricula']);
                if (isset($seen[$key])) {
                    $messages[] = 'La matrícula se repite en el archivo.';
                }
                $seen[$key] = true;
                if ($messages) {
                    $errors["fila_{$number}"] = "Fila {$number}: ".implode(' ', $messages);
                }
                $rows[$number] = $data;
            }
            if (! $rows) {
                throw ValidationException::withMessages(['archivo' => 'El archivo no contiene alumnos.']);
            }

            return DB::transaction(function () use ($rows, $errors): int {
                $existing = Alumno::query()->whereIn('matricula', array_column($rows, 'matricula'))->lockForUpdate()->pluck('matricula')->map(fn ($value) => Str::lower($value))->all();
                foreach ($rows as $number => $data) {
                    if (in_array(Str::lower($data['matricula']), $existing, true)) {
                        $errors["fila_{$number}"] = "Fila {$number}: la matrícula ya está registrada. No se modificó al alumno existente.";
                    }
                }
                if ($errors) {
                    throw ValidationException::withMessages(array_slice($errors, 0, 100, true));
                }
                foreach ($rows as $data) {
                    Alumno::create($data);
                }

                return count($rows);
            });
        } catch (Exception|\InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['archivo' => 'No se pudo leer el archivo. Guarda una copia válida en Excel e inténtalo de nuevo.']);
        } finally {
            $book?->disconnectWorksheets();
        }
    }
}
