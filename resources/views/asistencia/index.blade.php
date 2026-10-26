@extends('layouts.app')
@section('title', 'Asistencia')
@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Asistencia</li>
@endsection
@section('content')
<div class="ui-resource-page">
    <x-ui.page-header eyebrow="Seguimiento tutorial" title="Asistencia" :description="'Historial de pases de lista · '.$grupo->nombre">
        <a class="btn btn-primary" href="{{ route('asistencia.tomar-lista', $grupo->id) }}"><i class="fas fa-calendar-check" aria-hidden="true"></i> Tomar lista</a>
    </x-ui.page-header>
    <form class="card ui-form-card mb-4" method="GET">
        <div class="card-body ui-attendance-filter">
            <div class="form-group mb-0">
                <label for="id_grupo">Grupo</label>
                <select class="form-control" id="id_grupo" name="id_grupo">
                    @foreach ($grupos as $item)<option value="{{ $item->id }}" @selected($item->id === $grupo->id)>{{ $item->nombre }}</option>@endforeach
                </select>
            </div>
            <div class="ui-form-actions">
                <button class="btn btn-primary" type="submit"><i class="fas fa-search" aria-hidden="true"></i> Consultar</button>
                <a class="btn btn-outline-primary" href="{{ route('asistencia.exportar', $grupo->id) }}"><i class="fas fa-download" aria-hidden="true"></i> Exportar CSV</a>
            </div>
        </div>
    </form>
    <x-ui.list-panel title="Historial de asistencia">
        <table class="table ui-table">
            <thead><tr><th scope="col">Fecha</th><th scope="col">Asistencias</th><th scope="col">Faltas</th><th scope="col" class="text-right">Acciones</th></tr></thead>
            <tbody>
                @forelse ($registros as $registro)
                    <tr>
                        <td><strong>{{ $registro->fecha->format('d/m/Y') }}</strong></td><td>{{ $registro->total_asistencias }}</td><td>{{ $registro->total_faltas }}</td>
                        <td><div class="ui-inline-actions">
                            <a class="btn btn-outline-primary" href="{{ route('asistencia.view', [$grupo->id, $registro->fecha->format('Y-m-d')]) }}"><i class="fas fa-eye" aria-hidden="true"></i> Ver detalle</a>
                            <form method="POST" action="{{ route('asistencia.delete', [$grupo->id, $registro->fecha->format('Y-m-d')]) }}" onsubmit="return confirm('¿Eliminar este pase de lista?')">@csrf<button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash-alt" aria-hidden="true"></i> Eliminar</button></form>
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="ui-empty-state"><strong>No hay pases de lista.</strong>Selecciona Tomar lista para registrar la primera asistencia del grupo.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-ui.list-panel>
</div>
@endsection
