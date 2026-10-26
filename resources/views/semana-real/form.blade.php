@extends('layouts.app')
@section('title', isset($registro) ? 'Editar semana' : 'Registrar semana')
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('semana-real.index') }}">Semanas realizadas</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ isset($registro) ? 'Editar' : 'Registrar' }} semana</li>
@endsection
@section('content')
<div class="ui-resource-page">
    <x-ui.page-header eyebrow="Seguimiento tutorial" :title="isset($registro) ? 'Editar semana' : 'Registrar semana'" :description="$semana->nombre.' · '.$grupo->nombre">
        <a class="btn btn-outline-primary" href="{{ route('semana-real.index') }}"><i class="fas fa-arrow-left" aria-hidden="true"></i> Volver al historial</a>
    </x-ui.page-header>
    <form method="POST" enctype="multipart/form-data" class="card ui-form-card">
        @csrf
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger" role="alert"><strong>Revisa los datos antes de guardar.</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <fieldset class="ui-form-section">
                <legend>Atención del grupo</legend>
                <p class="ui-form-intro">Registra la atención y el número de alumnos participantes en esta semana.</p>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="semana_atendida">Semana atendida</label>
                        <input class="form-control" id="semana_atendida" name="semana_atendida" type="number" min="0" value="{{ old('semana_atendida', $registro->semana_atendida ?? 0) }}" required>
                    </div>
                    @foreach (['alumnos_atendidos' => 'Alumnos atendidos', 'alumnos_faltantes' => 'Alumnos faltantes', 'total_alumnos' => 'Total de alumnos', 'atendidos_hombres' => 'Hombres atendidos', 'atendidos_mujeres' => 'Mujeres atendidas', 'total_gatendidos' => 'Grupos atendidos'] as $campo => $label)
                        <div class="form-group col-md-3"><label for="semana-{{ $campo }}">{{ $label }}</label><input class="form-control" id="semana-{{ $campo }}" type="number" min="0" name="{{ $campo }}" value="{{ old($campo, $registro->$campo ?? 0) }}" required></div>
                    @endforeach
                </div>
            </fieldset>
            <fieldset class="ui-form-section">
                <legend>Detalle de la actividad</legend>
                <div class="form-row">
                    <div class="form-group col-md-6"><label for="semana-alumnos">Alumnos</label><textarea id="semana-alumnos" class="form-control" name="alumnos" placeholder="Anota los alumnos que participaron.">{{ old('alumnos', $registro->alumnos ?? '') }}</textarea></div>
                    <div class="form-group col-md-6"><label for="semana-observaciones">Observaciones</label><textarea id="semana-observaciones" class="form-control" name="observaciones" required placeholder="Describe las actividades y los resultados de la semana.">{{ old('observaciones', $registro->observaciones ?? '') }}</textarea></div>
                </div>
                <div class="form-group ui-upload">
                    <label for="semana-evidencias"><i class="fas fa-paperclip" aria-hidden="true"></i> Adjuntar evidencias</label>
                    <input id="semana-evidencias" class="form-control-file" type="file" name="evidencias[]" multiple accept=".jpg,.jpeg,.png,.pdf" aria-describedby="evidencias-ayuda">
                    <small class="form-text" id="evidencias-ayuda">Puedes seleccionar imágenes JPG, PNG o documentos PDF.</small>
                </div>
            </fieldset>
            <div class="ui-form-actions"><button class="btn btn-primary" type="submit"><i class="fas fa-save" aria-hidden="true"></i> Guardar semana</button><a class="btn btn-outline-secondary" href="{{ route('semana-real.index') }}">Cancelar</a></div>
        </div>
    </form>
</div>
@endsection
