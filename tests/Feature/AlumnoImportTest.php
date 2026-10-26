<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\GrupoMaster;
use App\Models\User;
use App\Services\AlumnoImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Tests\TestCase;

class AlumnoImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_import_and_download_template(): void
    {
        $this->actingAs($this->user('admin'))->get('/alumno/importar')->assertOk()->assertSee('Descargar plantilla Excel');
        $response = $this->get('/alumno/plantilla')->assertOk()->assertDownload('plantilla-alumnos.xlsx');
        $path = tempnam(sys_get_temp_dir(), 'template');
        file_put_contents($path, $response->streamedContent());
        try {
            $book = IOFactory::load($path);
            $this->assertSame(AlumnoImportService::COLUMNS, $book->getActiveSheet()->rangeToArray('A1:I1')[0]);
            $this->assertSame('00123456', $book->getActiveSheet()->getCell('D2')->getValue());
            $book->disconnectWorksheets();
        } finally {
            unlink($path);
        }
    }

    public function test_imports_xlsx_with_excel_dates_and_preserves_leading_zeroes(): void
    {
        $group = GrupoMaster::create(['id_periodo' => 1, 'id_carrera' => 1, 'id_semestre' => 1, 'id_grupoLetra' => 1]);
        $this->actingAs($this->user('admin'))->post('/alumno/importar', [
            'archivo' => $this->file([$this->row()], 'Xlsx', true),
            'id_grupo' => $group->id,
        ])->assertSessionHasNoErrors()->assertSessionHas('import_success')->assertRedirect('/alumno/importar');

        $this->assertDatabaseHas('alumno', ['matricula' => '00123456', 'telefono' => '0999123456', 'fecha_nac' => '2005-03-15', 'genero' => 1, 'id_grupo' => $group->id]);
    }

    public function test_imports_xls_and_csv_without_a_group(): void
    {
        $this->actingAs($this->user('admin'));
        foreach (['Xls', 'Csv'] as $format) {
            $row = $this->row();
            $row[3] = $format.'-001';
            $this->post('/alumno/importar', ['archivo' => $this->file([$row], $format)])
                ->assertSessionHasNoErrors()->assertSessionHas('import_success');
            $this->assertDatabaseHas('alumno', ['matricula' => $format.'-001', 'id_grupo' => 0]);
        }
    }

    public function test_invalid_row_prevents_the_entire_import(): void
    {
        $bad = $this->row();
        $bad[3] = 'OTHER';
        $bad[4] = 'invalid-email';
        $this->actingAs($this->user('admin'))->from('/alumno/importar')->post('/alumno/importar', [
            'archivo' => $this->file([$this->row(), $bad]),
        ])->assertSessionHasErrors('fila_3')->assertRedirect('/alumno/importar');
        $this->assertDatabaseCount('alumno', 0);
    }

    public function test_duplicate_matriculas_in_file_are_rejected(): void
    {
        $this->actingAs($this->user('admin'))->post('/alumno/importar', [
            'archivo' => $this->file([$this->row(), $this->row()]),
        ])->assertSessionHasErrors('fila_3');
        $this->assertDatabaseCount('alumno', 0);
    }

    public function test_existing_student_is_not_overwritten(): void
    {
        $data = array_combine(AlumnoImportService::COLUMNS, $this->row());
        $data['genero'] = 1;
        $student = Alumno::create($data + ['id_grupo' => 0]);
        $row = $this->row();
        $row[0] = 'Otro nombre';
        $this->actingAs($this->user('admin'))->post('/alumno/importar', [
            'archivo' => $this->file([$row]),
        ])->assertSessionHasErrors('fila_2');
        $this->assertSame('Ana', $student->fresh()->nombres);
        $this->assertDatabaseCount('alumno', 1);
    }

    public function test_rejects_missing_headers_empty_and_corrupt_files(): void
    {
        $this->actingAs($this->user('admin'));
        foreach ([
            $this->file([]),
            UploadedFile::fake()->createWithContent('alumnos.csv', "nombre,matricula\nAna,123\n"),
            UploadedFile::fake()->createWithContent('alumnos.xlsx', 'broken'),
        ] as $file) {
            $this->post('/alumno/importar', ['archivo' => $file])->assertSessionHasErrors('archivo');
        }
        $this->assertDatabaseCount('alumno', 0);
    }

    public function test_formulas_and_invalid_group_are_rejected(): void
    {
        $row = $this->row();
        $row[0] = '=1+1';
        $this->actingAs($this->user('admin'))->post('/alumno/importar', ['archivo' => $this->file([$row])])->assertSessionHasErrors('fila_2');
        $this->post('/alumno/importar', ['archivo' => $this->file([$this->row()]), 'id_grupo' => 999])->assertSessionHasErrors('id_grupo');
        $this->assertDatabaseCount('alumno', 0);
    }

    public function test_only_admin_can_import_or_download_the_template(): void
    {
        $this->get('/alumno/importar')->assertRedirect('/site/login');
        $this->actingAs($this->user('tutor'));
        $this->get('/alumno/importar')->assertForbidden();
        $this->get('/alumno/plantilla')->assertForbidden();
        $this->post('/alumno/importar', ['archivo' => $this->file([$this->row()])])->assertForbidden();
        $this->assertDatabaseCount('alumno', 0);
    }

    private function row(): array
    {
        return ['Ana', 'López', 'Pérez', '00123456', 'ana@example.com', '0999123456', '2005-03-15', 'Valladolid', 'Femenino'];
    }

    private function file(array $rows, string $format = 'Xlsx', bool $excelDate = false): UploadedFile
    {
        $book = new Spreadsheet;
        $sheet = $book->getActiveSheet();
        $sheet->fromArray(AlumnoImportService::COLUMNS);
        foreach ($rows as $index => $row) {
            foreach ($row as $column => $value) {
                $sheet->setCellValueExplicit([$column + 1, $index + 2], $value, str_starts_with($value, '=') ? DataType::TYPE_FORMULA : DataType::TYPE_STRING);
            }
        }
        if ($excelDate) {
            $sheet->setCellValue('G2', Date::PHPToExcel(new \DateTime('2005-03-15')));
            $sheet->getStyle('G2')->getNumberFormat()->setFormatCode('dd/mm/yyyy');
        }
        $path = tempnam(sys_get_temp_dir(), 'students');
        try {
            IOFactory::createWriter($book, $format)->save($path);

            return UploadedFile::fake()->createWithContent('alumnos.'.strtolower($format), file_get_contents($path));
        } finally {
            $book->disconnectWorksheets();
            unlink($path);
        }
    }

    private function user(string $role): User
    {
        $user = User::create([
            'username' => $role, 'auth_key' => str_repeat('a', 32),
            'password_hash' => Hash::make('secret'), 'email' => $role.'@example.com',
            'status' => User::STATUS_ACTIVE,
        ]);
        DB::table('auth_assignment')->insert(['item_name' => $role, 'user_id' => (string) $user->id]);

        return $user;
    }
}
