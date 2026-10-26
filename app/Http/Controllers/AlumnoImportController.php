<?php

namespace App\Http\Controllers;

use App\Models\GrupoMaster;
use App\Services\AlumnoImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AlumnoImportController extends Controller
{
    public function index(): View
    {
        return view('alumno.importar', [
            'grupos' => GrupoMaster::with(['carrera', 'semestre', 'grupoLetra', 'periodo'])->orderByDesc('id')->get(),
        ]);
    }

    public function store(Request $request, AlumnoImportService $importer): RedirectResponse
    {
        $validated = $request->validate([
            'archivo' => ['required', 'file', 'extensions:xlsx,xls,csv', 'mimes:xlsx,xls,csv,txt', 'max:5120'],
            'id_grupo' => ['nullable', 'integer', Rule::exists('grupo_master', 'id')],
        ]);
        $count = $importer->import($request->file('archivo')->getRealPath(), (int) ($validated['id_grupo'] ?? 0));

        return to_route('alumno.importar')->with('import_success', "Se importaron {$count} alumnos correctamente.");
    }

    public function template(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $book = new Spreadsheet;
            try {
                $sheet = $book->getActiveSheet();
                $sheet->setTitle('Alumnos');
                $sheet->fromArray(AlumnoImportService::COLUMNS);
                $example = ['Ana', 'López', 'Pérez', '00123456', 'ana@example.com', '9991234567', '2005-03-15', 'Valladolid', 'Femenino'];
                foreach ($example as $index => $value) {
                    $sheet->setCellValueExplicit([$index + 1, 2], $value, DataType::TYPE_STRING);
                }
                $sheet->getStyle('A1:I1')->getFont()->setBold(true);
                $sheet->getStyle('D2:G2001')->getNumberFormat()->setFormatCode('@');
                foreach (range('A', 'I') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
                $sheet->freezePane('A2');
                (new Xlsx($book))->save('php://output');
            } finally {
                $book->disconnectWorksheets();
            }
        }, 'plantilla-alumnos.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }
}
