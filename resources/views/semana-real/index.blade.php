@extends('layouts.app')
@section('title', 'Semanas realizadas')
@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Semanas realizadas</li>
@endsection
@section('content')
<div class="ui-resource-page">
    <x-ui.page-header eyebrow="Seguimiento tutorial" title="Semanas realizadas" description="Actividades y alumnos atendidos por semana.">
        <a class="btn btn-outline-primary" href="{{ url('/semana/index') }}"><i class="fas fa-calendar-alt" aria-hidden="true"></i> Consultar semanas del PAT</a>
    </x-ui.page-header>
    <x-ui.list-panel title="Historial de actividades" :paginator="$registros">
        <table class="table ui-table">
            <thead><tr><th scope="col">Semana</th><th scope="col">Grupo</th><th scope="col">Atendida</th><th scope="col">Alumnos atendidos</th><th scope="col">Observaciones</th><th scope="col" class="text-right">Acciones</th></tr></thead>
            <tbody>
                @forelse ($registros as $registro)
                    <tr>
                        <td><strong>{{ $registro->semana->nombre }}</strong></td><td>{{ $registro->grupo->nombre }}</td>
                        <td><x-ui.status-badge :status="$registro->semana_atendida ? 'active' : 'inactive'" :label="$registro->semana_atendida ? 'Sí' : 'No'" /></td>
                        <td>{{ $registro->alumnos_atendidos }}</td><td class="ui-cell-note">{{ $registro->observaciones }}</td>
                        <td><div class="ui-inline-actions">
                            <a class="btn btn-outline-primary" href="{{ route('semana-real.edit', $registro->id) }}"><i class="fas fa-pencil-alt" aria-hidden="true"></i> Editar</a>
                            <form method="POST" action="{{ route('semana-real.delete', $registro->id) }}" onsubmit="return confirm('¿Eliminar este registro?')">@csrf<button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash-alt" aria-hidden="true"></i> Eliminar</button></form>
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="ui-empty-state"><strong>Aún no hay actividades registradas</strong>Consulta las semanas del PAT para registrar la atención de tu grupo.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-ui.list-panel>
</div>
@endsection
