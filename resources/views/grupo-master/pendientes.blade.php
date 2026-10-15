@extends('layouts.app')
@section('title', 'Expedientes pendientes')
@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Expedientes pendientes</li>
@endsection
@section('content')
<div class="ui-resource-page">
    <x-ui.page-header title="Expedientes pendientes" eyebrow="Seguimiento tutorial" description="Consulta quiénes todavía no han enviado su expediente. Los enviados y aprobados no aparecen en esta lista." />
    <form method="get" action="{{ url('/grupo-master/pendientes') }}" class="card card-body mb-4">
        <div class="form-row align-items-end">
            <div class="form-group col-md-5 mb-md-0">
                <label for="pendingGroup">Grupo</label>
                <select class="form-control" id="pendingGroup" name="id_grupo">
                    <option value="">{{ $isAdmin ? 'Todos los grupos y alumnos sin grupo' : 'Todos mis grupos' }}</option>
                    @foreach($grupos as $item)
                        <option value="{{ $item->id }}" @selected($selectedId == $item->id)>{{ $item->nombre ?: 'Grupo '.$item->id }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-5 mb-md-0">
                <label for="pendingSearch">Nombre o matrícula</label>
                <input class="form-control" id="pendingSearch" name="buscar" value="{{ $buscar }}" placeholder="Buscar alumno">
            </div>
            <div class="col-md-2"><button class="btn btn-primary w-100" type="submit">Filtrar</button></div>
        </div>
    </form>
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap:12px">
        <strong>{{ $pendientes->total() }} {{ $pendientes->total() === 1 ? 'alumno pendiente' : 'alumnos pendientes' }}</strong>
        @if(!$isAdmin && $grupo && $pendientes->total())
            <form method="post" action="{{ url('/grupo-master/enviar-pendientes') }}" onsubmit="return confirm('¿Crear un aviso y enviar un correo a los alumnos pendientes de este grupo?')">
                @csrf
                <input type="hidden" name="id_grupo" value="{{ $grupo->id }}">
                <button class="btn btn-outline-primary" type="submit">Notificar al grupo</button>
            </form>
        @endif
    </div>
    <div class="card ui-table-card">
        <div class="table-responsive">
            <table class="table ui-table mb-0">
                <thead><tr><th>Alumno</th><th>Matrícula</th><th>Grupo / tutora o tutor</th><th>Correo</th><th>Situación</th></tr></thead>
                <tbody>
                @forelse($pendientes as $alumno)
                    <tr>
                        <td><strong>{{ $alumno->nombre_completo }}</strong></td>
                        <td>{{ $alumno->matricula }}</td>
                        <td>{{ $alumno->grupo?->nombre ?: 'Sin grupo asignado' }}<small class="d-block text-muted">{{ $alumno->grupo?->tutor?->nombre_completo ?: 'Sin tutor asignado' }}</small></td>
                        <td>{{ $alumno->correo ?: 'Sin correo registrado' }}</td>
                        <td><x-ui.status-badge status="warning" :label="$alumno->latestExpediente ? 'Sin enviar · '.$alumno->latestExpediente->estatus : 'Sin expediente'" /></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="ui-empty-state">{{ $buscar || $selectedId ? 'No hay alumnos pendientes con estos filtros.' : 'No hay expedientes pendientes en los grupos disponibles.' }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $pendientes->links() }}</div>
</div>
@endsection
