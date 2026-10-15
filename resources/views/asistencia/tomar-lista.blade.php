@extends('layouts.app')

@section('title', 'Tomar lista')

@section('content')
    <h1 class="mb-3">Tomar lista · {{ $grupo->nombre }}</h1>
    <form method="POST">
        @csrf
        <div class="form-group">
            <label for="fecha">Fecha</label>
            <input class="form-control" id="fecha" name="fecha" type="date" value="{{ old('fecha', $fecha) }}" required>
        </div>
        <div class="card mb-3"><div class="table-responsive"><table class="table mb-0">
            <thead><tr><th>Alumno</th><th>Presente</th></tr></thead>
            <tbody>
            @foreach ($grupo->alumnos->sortBy('apellidop') as $alumno)
                <tr>
                    <td>{{ $alumno->nombre_completo }}</td>
                    <td><input type="hidden" name="estado[{{ $alumno->id }}]" value="0"><input type="checkbox" name="estado[{{ $alumno->id }}]" value="1" @checked(old("estado.{$alumno->id}", $asistencias[$alumno->id] ?? false))></td>
                </tr>
            @endforeach
            </tbody>
        </table></div></div>
        <button class="btn btn-primary">Guardar asistencia</button>
        <a class="btn btn-secondary" href="{{ route('asistencia.index', ['id_grupo' => $grupo->id]) }}">Cancelar</a>
    </form>
@endsection
