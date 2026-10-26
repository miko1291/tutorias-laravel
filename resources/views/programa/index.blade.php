@extends('layouts.app')

@section('title', $titulo)

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">{{ $titulo }}</li>
@endsection

@section('content')
    <div class="ui-resource-page">
        <x-ui.page-header eyebrow="Seguimiento tutorial" :title="$titulo" description="Registra y consulta la información de seguimiento de tus alumnos." />
    @if($formulario)
        <div class="card ui-form-card mb-4">
            <div class="card-body"><h2 class="ui-form-card__title"><i class="fas fa-plus-circle" aria-hidden="true"></i> Nuevo registro</h2><p class="ui-form-intro">Selecciona al alumno y completa los datos del seguimiento.</p>
                <form method="POST" action="{{ url('/'.$formulario.'/guardar') }}">
                    @csrf
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Alumno</label>
                            <select name="id_alumno" class="form-control" required>
                                <option value="">Selecciona un alumno</option>
                                @foreach($alumnos as $alumno)
                                    <option value="{{ $alumno->id }}">{{ $alumno->nombre_completo }} ({{ $alumno->matricula }})</option>
                                @endforeach
                            </select>
                        </div>
                        @if($formulario === 'diagnostico')
                            <div class="form-group col-md-2"><label>Motivo</label><select name="motivo" class="form-control">@foreach([1=>'Repetición',2=>'Atrasada',3=>'Especial',4=>'Global'] as $key=>$value)<option value="{{ $key }}">{{ $value }}</option>@endforeach</select></div>
                            @foreach(['asignaturas', 'otro', 'especifique'] as $campo)<div class="form-group col-md-2"><label>{{ ucfirst($campo) }}</label><input name="{{ $campo }}" class="form-control" required></div>@endforeach
                        @elseif($formulario === 'evaluacion')
                            <div class="form-group col-md-4"><label>Criterio</label><select name="id_criterio" class="form-control">@foreach($criterios as $criterio)<option value="{{ $criterio->id }}">{{ $criterio->nombre }}</option>@endforeach</select></div>
                            <div class="form-group col-md-2"><label>Calificación</label><input name="calificacion" type="number" min="0" max="100" class="form-control" required></div>
                        @else
                            <div class="form-group col-md-2"><label>Estado</label><select name="estatus" class="form-control"><option value="0">No canalizado</option><option value="1">Canalizado</option></select></div>
                            <div class="form-group col-md-3"><label>Asunto</label><input name="asunto" class="form-control" required></div>
                            <div class="form-group col-md-3"><label>Cuerpo</label><textarea name="cuerpo" class="form-control" required></textarea></div>
                        @endif
                    </div>
                    <div class="ui-form-actions"><button class="btn btn-primary" type="submit"><i class="fas fa-save" aria-hidden="true"></i> Guardar registro</button></div>
                </form>
            </div>
        </div>
    @endif

    <x-ui.list-panel title="Registros de seguimiento"><table class="table ui-table mb-0">
        <thead><tr>@foreach($columnas as $columna)<th>{{ $columna }}</th>@endforeach</tr></thead>
        <tbody>@forelse($filas as $fila)<tr>@foreach($fila as $valor)<td>{{ $valor }}</td>@endforeach</tr>@empty<tr><td colspan="{{ count($columnas) }}" class="ui-empty-state"><strong>No hay registros.</strong>Los registros de seguimiento aparecerán aquí.</td></tr>@endforelse</tbody>
    </table></x-ui.list-panel></div>
@endsection
