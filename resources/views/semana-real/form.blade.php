@extends('layouts.app')

@section('title', isset($registro) ? 'Editar semana' : 'Registrar semana')

@section('content')
    <h1 class="mb-3">{{ isset($registro) ? 'Editar' : 'Registrar' }} semana · {{ $semana->nombre }} · {{ $grupo->nombre }}</h1>
    <form method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-row">
            @foreach (['semana_atendida' => 'Semana atendida', 'alumnos_atendidos' => 'Alumnos atendidos', 'alumnos_faltantes' => 'Alumnos faltantes', 'total_alumnos' => 'Total alumnos', 'atendidos_hombres' => 'Atendidos hombres', 'atendidos_mujeres' => 'Atendidos mujeres', 'total_gatendidos' => 'Total grupos atendidos'] as $campo => $label)
                <div class="form-group col-md-3"><label>{{ $label }}</label><input class="form-control" type="number" min="0" name="{{ $campo }}" value="{{ old($campo, $registro->$campo ?? 0) }}" required></div>
            @endforeach
        </div>
        <div class="form-group"><label>Alumnos</label><textarea class="form-control" name="alumnos">{{ old('alumnos', $registro->alumnos ?? '') }}</textarea></div>
        <div class="form-group"><label>Observaciones</label><textarea class="form-control" name="observaciones" required>{{ old('observaciones', $registro->observaciones ?? '') }}</textarea></div>
        <div class="form-group"><label>Evidencias</label><input class="form-control-file" type="file" name="evidencias[]" multiple accept=".jpg,.jpeg,.png,.pdf"></div>
        <button class="btn btn-primary">Guardar</button>
        <a class="btn btn-secondary" href="{{ route('semana-real.index') }}">Cancelar</a>
    </form>
@endsection
