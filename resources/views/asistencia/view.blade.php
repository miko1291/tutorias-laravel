@extends('layouts.app')
@section('title', 'Detalle de asistencia')
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('asistencia.index', ['id_grupo' => $grupo->id]) }}">Asistencia</a></li>
    <li class="breadcrumb-item active" aria-current="page">Detalle</li>
@endsection
@section('content')
<div class="ui-resource-page">
    <x-ui.page-header eyebrow="Seguimiento tutorial" title="Detalle de asistencia" :description="$grupo->nombre.' · '.$fecha">
        <a class="btn btn-outline-primary" href="{{ route('asistencia.index', ['id_grupo' => $grupo->id]) }}"><i class="fas fa-arrow-left" aria-hidden="true"></i> Volver al historial</a>
    </x-ui.page-header>
    <dl class="ui-summary-grid">
        <div class="ui-summary-item"><dt>Fecha del pase de lista</dt><dd>{{ $fecha }}</dd></div>
        <div class="ui-summary-item"><dt>Asistencias</dt><dd>{{ $registros->where('estado', 1)->count() }}</dd></div>
        <div class="ui-summary-item"><dt>Faltas</dt><dd>{{ $registros->where('estado', 0)->count() }}</dd></div>
    </dl>
    <x-ui.list-panel title="Asistencia por alumno">
        <table class="table ui-table">
            <thead><tr><th scope="col">Alumno</th><th scope="col">Estado</th></tr></thead>
            <tbody>
                @forelse ($registros as $registro)
                    <tr><td>{{ $registro->alumno->nombre_completo }}</td><td><x-ui.status-badge :status="$registro->estado ? 'active' : 'danger'" :label="$registro->estado ? 'Presente' : 'Falta'" /></td></tr>
                @empty
                    <tr><td colspan="2" class="ui-empty-state"><strong>Sin asistencia registrada</strong>No hay registros para esa fecha.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-ui.list-panel>
</div>
@endsection
