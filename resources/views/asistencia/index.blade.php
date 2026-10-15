@extends('layouts.app')

@section('title', 'Asistencia')

@section('content')
    <div class="ui-resource-page">
        <x-ui.page-header eyebrow="Seguimiento tutorial" title="Asistencia" :description="'Registro de asistencia · '.$grupo->nombre">
            <a class="btn btn-primary" href="{{ route('asistencia.tomar-lista', $grupo->id) }}"><i class="fas fa-calendar-check"></i> Tomar lista</a>
        </x-ui.page-header>
        <form class="card card-body ui-filter-bar mb-3" method="GET">
            <label class="mb-0 mr-2 font-weight-bold" for="id_grupo">Grupo</label>
            <select class="form-control mr-2" id="id_grupo" name="id_grupo" onchange="this.form.submit()">
            @foreach ($grupos as $item)
                <option value="{{ $item->id }}" @selected($item->id === $grupo->id)>{{ $item->nombre }}</option>
            @endforeach
            </select>
            <a class="btn btn-outline-secondary" href="{{ route('asistencia.exportar', $grupo->id) }}"><i class="fas fa-download"></i> Exportar CSV</a>
        </form>
    <div class="card ui-table-card"><div class="table-responsive"><table class="table ui-table mb-0">
        <thead><tr><th>Fecha</th><th>Asistencias</th><th>Faltas</th><th></th></tr></thead>
        <tbody>
        @forelse ($registros as $registro)
            <tr>
                <td>{{ $registro->fecha->format('Y-m-d') }}</td>
                <td>{{ $registro->total_asistencias }}</td>
                <td>{{ $registro->total_faltas }}</td>
                <td class="text-right">
                    <x-ui.action-menu><a class="dropdown-item" href="{{ route('asistencia.view', [$grupo->id, $registro->fecha->format('Y-m-d')]) }}"><i class="fas fa-eye"></i> Ver detalle</a>
                    <form class="d-inline" method="POST" action="{{ route('asistencia.delete', [$grupo->id, $registro->fecha->format('Y-m-d')]) }}">
                        @csrf
                        <button class="dropdown-item text-danger" onclick="return confirm('¿Eliminar este pase de lista?')"><i class="fas fa-trash"></i> Eliminar</button>
                    </form></x-ui.action-menu>
                </td>
            </tr>
        @empty
            <tr><td colspan="4">No hay pases de lista.</td></tr>
        @endforelse
        </tbody>
    </table>    </div></div></div>
@endsection

@push('styles')
<style>.ui-filter-bar{display:flex;align-items:center;gap:8px}@media(max-width:600px){.ui-filter-bar{display:block}.ui-filter-bar select,.ui-filter-bar a{width:100%;margin:8px 0 0!important}}</style>
@endpush
