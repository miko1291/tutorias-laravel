<?php

namespace App\Http\Controllers;

use App\Models\GrupoMaster;
use App\Models\Semana;
use App\Models\SemanaReal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SemanaRealController extends Controller
{
    public function index()
    {
        $registros = SemanaReal::with(['grupo', 'semana'])
            ->whereIn('id_grupomaster', $this->gruposVisibles()->pluck('id'))
            ->latest('id')
            ->paginate(30);

        return view('semana-real.index', compact('registros'));
    }

    public function create(Request $request, int $idSemana, int $idGrupo)
    {
        $grupo = $this->grupoVisible($idGrupo);
        $semana = Semana::findOrFail($idSemana);

        if ($request->isMethod('post')) {
            $data = $this->validated($request);
            $data['id_semana'] = $semana->id;
            $data['id_grupomaster'] = $grupo->id;
            $data['evidencias'] = $this->storeEvidence($request) ?? '';
            SemanaReal::create($data);

            return redirect()->route('semana-real.index')->with('success', 'Semana registrada correctamente.');
        }

        return view('semana-real.form', compact('grupo', 'semana'));
    }

    public function update(Request $request, int $id)
    {
        $registro = SemanaReal::findOrFail($id);
        $this->grupoVisible($registro->id_grupomaster);

        if ($request->isMethod('post')) {
            $data = $this->validated($request);
            $newEvidence = $this->storeEvidence($request);
            if ($newEvidence !== null) {
                $this->deleteEvidence($registro->evidencias);
                $data['evidencias'] = $newEvidence;
            }
            $registro->update($data);

            return redirect()->route('semana-real.index')->with('success', 'Semana actualizada correctamente.');
        }

        $grupo = $registro->grupo;
        $semana = $registro->semana;

        return view('semana-real.form', compact('registro', 'grupo', 'semana'));
    }

    public function delete(int $id)
    {
        $registro = SemanaReal::findOrFail($id);
        $this->grupoVisible($registro->id_grupomaster);
        $this->deleteEvidence($registro->evidencias);
        $registro->delete();

        return back()->with('success', 'Registro eliminado correctamente.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'semana_atendida' => ['required', 'integer', 'min:0'],
            'alumnos_atendidos' => ['required', 'integer', 'min:0'],
            'alumnos_faltantes' => ['required', 'integer', 'min:0'],
            'total_alumnos' => ['required', 'integer', 'min:0'],
            'atendidos_hombres' => ['required', 'integer', 'min:0'],
            'atendidos_mujeres' => ['required', 'integer', 'min:0'],
            'total_gatendidos' => ['required', 'integer', 'min:0'],
            'observaciones' => ['required', 'string'],
            'alumnos' => ['nullable', 'string'],
            'evidencias.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);
    }

    private function storeEvidence(Request $request): ?string
    {
        $files = $request->file('evidencias', []);
        if ($files === []) {
            return null;
        }

        return collect($files)->map(fn ($file): string => $file->store('evidencias'))->implode(';');
    }

    private function deleteEvidence(?string $paths): void
    {
        foreach (array_filter(explode(';', (string) $paths)) as $path) {
            Storage::delete($path);
        }
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
