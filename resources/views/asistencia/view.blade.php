@extends('layouts.app')

@section('title', 'Detalle de asistencia')

@section('content')
    <h1>Asistencia del {{ $fecha }} · {{ $grupo->nombre }}</h1>
    <div class="card"><div class="table-responsive"><table class="table mb-0">
        <thead><tr><th>Alumno</th><th>Estado</th></tr></thead>
        <tbody>@forelse ($registros as $registro)
            <tr><td>{{ $registro->alumno->nombre_completo }}</td><td>{{ $registro->estado ? 'Presente' : 'Falta' }}</td></tr>
        @empty
            <tr><td colspan="2">No hay registros para esa fecha.</td></tr>
        @endforelse</tbody>
    </table></div></div>
@endsection
