@extends('layouts.app')
@section('title', 'Tomar lista')
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('asistencia.index', ['id_grupo' => $grupo->id]) }}">Asistencia</a></li>
    <li class="breadcrumb-item active" aria-current="page">Tomar lista</li>
@endsection
@section('content')
<div class="ui-resource-page">
    <x-ui.page-header eyebrow="Seguimiento tutorial" title="Tomar lista" :description="$grupo->nombre">
        <a class="btn btn-outline-primary" href="{{ route('asistencia.index', ['id_grupo' => $grupo->id]) }}"><i class="fas fa-arrow-left" aria-hidden="true"></i> Volver al historial</a>
    </x-ui.page-header>
    <form method="POST">
        @csrf
        <div class="card ui-form-card mb-4"><div class="card-body">
            <h2 class="ui-form-card__title"><i class="fas fa-calendar-check" aria-hidden="true"></i> Fecha de asistencia</h2>
            <p class="ui-form-intro">Selecciona la fecha y marca a los alumnos presentes antes de guardar.</p>
            <div class="form-group ui-attendance-date mb-0">
                <label for="fecha">Fecha del pase de lista</label>
                <input class="form-control @error('fecha') is-invalid @enderror" id="fecha" name="fecha" type="date" value="{{ old('fecha', $fecha) }}" required>
                @error('fecha')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div></div>
        <x-ui.list-panel title="Lista del grupo">
            <table class="table ui-table">
                <thead><tr><th scope="col">Alumno</th><th scope="col">Asistencia</th></tr></thead>
                <tbody>
                    @forelse ($grupo->alumnos->sortBy('apellidop') as $alumno)
                        <tr><td><strong>{{ $alumno->nombre_completo }}</strong><small class="d-block text-muted">{{ $alumno->matricula }}</small></td>
                            <td>
                                <input type="hidden" name="estado[{{ $alumno->id }}]" value="0">
                                <label class="ui-attendance-check" for="presente-{{ $alumno->id }}"><input id="presente-{{ $alumno->id }}" type="checkbox" name="estado[{{ $alumno->id }}]" value="1" aria-label="Presente: {{ $alumno->nombre_completo }}" @checked(old("estado.{$alumno->id}", $asistencias[$alumno->id] ?? false))> Presente</label>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="ui-empty-state"><strong>Este grupo aún no tiene alumnos</strong>Asigna alumnos al grupo para registrar la asistencia.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-ui.list-panel>
        <div class="ui-form-actions mt-3">
            <button class="btn btn-primary" type="submit" @disabled($grupo->alumnos->isEmpty())><i class="fas fa-save" aria-hidden="true"></i> Guardar asistencia</button>
            <a class="btn btn-outline-secondary" href="{{ route('asistencia.index', ['id_grupo' => $grupo->id]) }}">Cancelar</a>
        </div>
    </form>
</div>
@endsection
