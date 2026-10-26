@extends('layouts.app')
@section('title', 'Editar '.$tipo)
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/'.$tipo.'/index') }}">{{ ucfirst($tipo) }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Editar registro</li>
@endsection
@section('content')
<div class="ui-resource-page">
    <x-ui.page-header eyebrow="Seguimiento tutorial" :title="'Editar '.ucfirst($tipo)" description="Actualiza el registro de seguimiento del alumno.">
        <a class="btn btn-outline-primary" href="{{ url('/'.$tipo.'/index') }}"><i class="fas fa-arrow-left" aria-hidden="true"></i> Volver al listado</a>
    </x-ui.page-header>
    <form method="POST" action="{{ url('/'.$tipo.'/actualizar/'.$item->id) }}" class="card ui-form-card">
        @csrf
        <div class="card-body">
            <h2 class="ui-form-card__title"><i class="fas fa-clipboard" aria-hidden="true"></i> Datos del seguimiento</h2>
            @if($errors->any())
                <div class="alert alert-danger" role="alert"><strong>Revisa los campos antes de guardar.</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <div class="form-row">
                <div class="form-group col-md-6"><label for="seguimiento-alumno">Alumno</label><select id="seguimiento-alumno" class="form-control" disabled>@foreach($alumnos as $alumno)<option value="{{ $alumno->id }}" @selected($item->id_alumno === $alumno->id)>{{ $alumno->nombre_completo }}</option>@endforeach</select><small class="form-text">Este registro está vinculado al alumno seleccionado.</small></div>
                @if($tipo === 'diagnostico')
                    <div class="form-group col-md-6"><label for="seguimiento-motivo">Motivo</label><select id="seguimiento-motivo" name="motivo" class="form-control">@foreach([1 => 'Repetición', 2 => 'Atrasada', 3 => 'Especial', 4 => 'Global'] as $key => $value)<option value="{{ $key }}" @selected((string) old('motivo', $item->motivo) === (string) $key)>{{ $value }}</option>@endforeach</select></div>
                    @foreach(['asignaturas' => 'Asignaturas', 'otro' => 'Otro', 'especifique' => 'Especifique'] as $field => $label)
                        <div class="form-group col-md-4"><label for="seguimiento-{{ $field }}">{{ $label }}</label><input id="seguimiento-{{ $field }}" class="form-control" name="{{ $field }}" value="{{ old($field, $item->$field) }}" required></div>
                    @endforeach
                @elseif($tipo === 'evaluacion')
                    <div class="form-group col-md-4"><label for="seguimiento-criterio">Criterio</label><select id="seguimiento-criterio" name="id_criterio" class="form-control">@foreach($criterios as $criterio)<option value="{{ $criterio->id }}" @selected((string) old('id_criterio', $item->id_criterio) === (string) $criterio->id)>{{ $criterio->nombre }}</option>@endforeach</select></div>
                    <div class="form-group col-md-2"><label for="seguimiento-calificacion">Calificación</label><input id="seguimiento-calificacion" class="form-control" type="number" min="0" max="100" name="calificacion" value="{{ old('calificacion', $item->calificacion) }}" required></div>
                @else
                    <div class="form-group col-md-6"><label for="seguimiento-estatus">Estado</label><select id="seguimiento-estatus" name="estatus" class="form-control"><option value="0" @selected((string) old('estatus', $item->estatus) === '0')>No canalizado</option><option value="1" @selected((string) old('estatus', $item->estatus) === '1')>Canalizado</option></select></div>
                    <div class="form-group col-12"><label for="seguimiento-asunto">Asunto</label><input id="seguimiento-asunto" class="form-control" name="asunto" value="{{ old('asunto', $item->asunto) }}" required></div>
                    <div class="form-group col-12"><label for="seguimiento-cuerpo">Cuerpo</label><textarea id="seguimiento-cuerpo" class="form-control" name="cuerpo" required>{{ old('cuerpo', $item->cuerpo) }}</textarea></div>
                @endif
            </div>
            <div class="ui-form-actions"><button type="submit" class="btn btn-primary"><i class="fas fa-save" aria-hidden="true"></i> Guardar cambios</button><a class="btn btn-outline-secondary" href="{{ url('/'.$tipo.'/index') }}">Cancelar</a></div>
        </div>
    </form>
</div>
@endsection
