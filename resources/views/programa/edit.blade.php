@extends('layouts.app')
@section('title', 'Editar '.$tipo)
@section('content')
<h1 class="mb-3">Editar {{ ucfirst($tipo) }}</h1>
<form method="POST" action="{{ url('/'.$tipo.'/actualizar/'.$item->id) }}">
@csrf
<div class="form-group"><label>Alumno</label><select class="form-control" disabled>@foreach($alumnos as $alumno)<option value="{{ $alumno->id }}" @selected($item->id_alumno === $alumno->id)>{{ $alumno->nombre_completo }}</option>@endforeach</select></div>
@if($tipo === 'diagnostico')
<div class="form-group"><label>Motivo</label><select name="motivo" class="form-control">@foreach([1=>'Repetición',2=>'Atrasada',3=>'Especial',4=>'Global'] as $key=>$value)<option value="{{ $key }}" @selected($item->motivo === $key)>{{ $value }}</option>@endforeach</select></div>
@foreach(['asignaturas','otro','especifique'] as $field)<div class="form-group"><label>{{ ucfirst($field) }}</label><input class="form-control" name="{{ $field }}" value="{{ $item->$field }}" required></div>@endforeach
@elseif($tipo === 'evaluacion')
<div class="form-group"><label>Criterio</label><select name="id_criterio" class="form-control">@foreach($criterios as $criterio)<option value="{{ $criterio->id }}" @selected($item->id_criterio === $criterio->id)>{{ $criterio->nombre }}</option>@endforeach</select></div><div class="form-group"><label>Calificación</label><input class="form-control" type="number" min="0" max="100" name="calificacion" value="{{ $item->calificacion }}" required></div>
@else
<div class="form-group"><label>Estado</label><select name="estatus" class="form-control"><option value="0" @selected(!$item->estatus)>No canalizado</option><option value="1" @selected($item->estatus)>Canalizado</option></select></div><div class="form-group"><label>Asunto</label><input class="form-control" name="asunto" value="{{ $item->asunto }}" required></div><div class="form-group"><label>Cuerpo</label><textarea class="form-control" name="cuerpo" required>{{ $item->cuerpo }}</textarea></div>
@endif
<button class="btn btn-primary">Guardar cambios</button>
</form>
@endsection
