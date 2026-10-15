<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\GrupoMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AsistenciaController extends Controller
{
    public function index(Request $request)
    {
        $grupos = $this->gruposVisibles();
        $grupoId = (int) $request->query('id_grupo', $grupos->first()?->id);
        $grupo = $grupos->firstWhere('id', $grupoId);

        abort_unless($grupo, 404);

        $registros = Asistencia::where('id_grupo', $grupo->id)
            ->select('fecha')
            ->selectRaw('SUM(estado) as total_asistencias')
            ->selectRaw('COUNT(*) - SUM(estado) as total_faltas')
            ->groupBy('fecha')
            ->orderByDesc('fecha')
            ->get();

        return view('asistencia.index', compact('grupos', 'grupo', 'registros'));
    }

    public function tomarLista(Request $request, int $idGrupo)
    {
        $grupo = $this->grupoVisible($idGrupo);
        $fecha = $request->input('fecha', now()->toDateString());
        $asistencias = Asistencia::where('id_grupo', $grupo->id)
            ->whereDate('fecha', $fecha)
            ->pluck('estado', 'id_alumno');

        if ($request->isMethod('post')) {
            $data = $request->validate([
                'fecha' => ['required', 'date'],
                'estado' => ['required', 'array'],
                'estado.*' => ['required', 'boolean'],
            ]);

            DB::transaction(function () use ($grupo, $data): void {
                foreach ($grupo->alumnos as $alumno) {
                    if (! array_key_exists((string) $alumno->id, $data['estado'])) {
                        continue;
                    }

                    Asistencia::updateOrCreate(
                        ['id_alumno' => $alumno->id, 'id_grupo' => $grupo->id, 'fecha' => $data['fecha']],
                        ['estado' => (bool) $data['estado'][(string) $alumno->id]],
                    );
                }
            });

            return redirect()->route('asistencia.index', ['id_grupo' => $grupo->id])
                ->with('success', 'Asistencia registrada correctamente.');
        }

        return view('asistencia.tomar-lista', compact('grupo', 'fecha', 'asistencias'));
    }

    public function view(int $idGrupo, string $fecha)
    {
        $grupo = $this->grupoVisible($idGrupo);
        $registros = Asistencia::with('alumno')
            ->where('id_grupo', $grupo->id)
            ->whereDate('fecha', $fecha)
            ->orderBy('id')
            ->get();

        return view('asistencia.view', compact('grupo', 'fecha', 'registros'));
    }

    public function delete(int $idGrupo, string $fecha)
    {
        $grupo = $this->grupoVisible($idGrupo);
        Asistencia::where('id_grupo', $grupo->id)->whereDate('fecha', $fecha)->delete();

        return back()->with('success', 'El pase de lista fue eliminado.');
    }

    public function exportarReporte(int $idGrupo): StreamedResponse
    {
        $grupo = $this->grupoVisible($idGrupo);
        $registros = Asistencia::where('id_grupo', $grupo->id)
            ->select('fecha')
            ->selectRaw('SUM(estado) as total_asistencias')
            ->selectRaw('COUNT(*) - SUM(estado) as total_faltas')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        return response()->streamDownload(function () use ($registros): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Fecha', 'Total asistencias', 'Total faltas']);
            foreach ($registros as $registro) {
                fputcsv($handle, [$registro->fecha, $registro->total_asistencias, $registro->total_faltas]);
            }
            fclose($handle);
        }, 'reporte_asistencia.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="reporte_asistencia.csv"',
        ]);
    }

    private function gruposVisibles()
    {
        $query = GrupoMaster::with(['carrera', 'semestre', 'grupoLetra'])->orderBy('id');

        if (! Auth::user()->hasAnyRole(['admin', 'psicologo'])) {
            $query->where('id_tutor', Auth::user()->tutor?->id ?? 0);
        }

        return $query->get();
    }

    private function grupoVisible(int $idGrupo): GrupoMaster
    {
        $grupo = $this->gruposVisibles()->firstWhere('id', $idGrupo);

        abort_unless($grupo, 403, 'No tienes permiso para este grupo.');

        return $grupo;
    }
}
