@extends('layouts.app')

@section('title', 'Semanas realizadas')

@section('content')
    <h1 class="mb-3">Semanas realizadas</h1>
    <div class="card"><div class="table-responsive"><table class="table mb-0">
        <thead><tr><th>Semana</th><th>Grupo</th><th>Atendida</th><th>Atendidos</th><th>Observaciones</th><th></th></tr></thead>
        <tbody>@forelse ($registros as $registro)
            <tr>
                <td>{{ $registro->semana->nombre }}</td><td>{{ $registro->grupo->nombre }}</td>
                <td>{{ $registro->semana_atendida ? 'Sí' : 'No' }}</td><td>{{ $registro->alumnos_atendidos }}</td>
                <td>{{ $registro->observaciones }}</td>
                <td><a href="{{ route('semana-real.edit', $registro->id) }}">Editar</a>
                    <form class="d-inline" method="POST" action="{{ route('semana-real.delete', $registro->id) }}">@csrf<button class="btn btn-link text-danger" onclick="return confirm('¿Eliminar este registro?')">Eliminar</button></form>
                </td>
            </tr>
        @empty<tr><td colspan="6">No hay registros.</td></tr>@endforelse</tbody>
    </table></div></div>
    {{ $registros->links() }}
@endsection
