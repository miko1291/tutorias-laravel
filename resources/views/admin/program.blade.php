@extends('layouts.app')
@section('title', $title)
@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
@endsection
@php
    $formTitle = ['pat' => 'Crear programa de acción tutorial', 'semana' => 'Crear semana del PAT', 'performance' => 'Registrar desempeño'][$kind];
@endphp
@section('content')
<div class="ui-resource-page">
    <x-ui.page-header eyebrow="Administración académica" :title="$title" description="Registra las actividades y los resultados de los grupos." />
    <form method="POST" action="{{ url('/admin/'.['pat' => 'pat', 'semana' => 'semanas', 'performance' => 'performance'][$kind].'/save') }}" class="card ui-form-card mb-4">
        @csrf
        <div class="card-body">
            <h2 class="ui-form-card__title"><i class="fas fa-plus-circle" aria-hidden="true"></i> {{ $formTitle }}</h2>
            <p class="ui-form-intro">Completa los campos y guarda el registro.</p>
            @if($errors->any())
                <div class="alert alert-danger" role="alert"><strong>Revisa los campos del formulario.</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            @if($kind === 'pat')
                <div class="form-row">
                    <div class="form-group col-md-4"><label for="pat-semestre">Semestre</label><select id="pat-semestre" name="id_semestre" class="form-control">@foreach($semestres as $item)<option value="{{ $item->id }}" @selected((string) old('id_semestre') === (string) $item->id)>{{ $item->nombre }}</option>@endforeach</select></div>
                    <div class="form-group col-md-6"><label for="pat-nombre">Nombre del programa</label><input id="pat-nombre" name="nombre" class="form-control" value="{{ old('nombre') }}" required></div>
                    <div class="form-group col-md-2"><label for="pat-estatus">Estado</label><select id="pat-estatus" name="estatus" class="form-control"><option value="1" @selected((string) old('estatus', '1') === '1')>Activo</option><option value="0" @selected((string) old('estatus', '1') === '0')>Inactivo</option></select></div>
                </div>
                <div class="form-group"><label for="pat-descripcion">Descripción</label><textarea id="pat-descripcion" name="descripcion" class="form-control" placeholder="Describe el propósito del programa.">{{ old('descripcion') }}</textarea></div>
            @elseif($kind === 'semana')
                <div class="form-row">
                    <div class="form-group col-md-2"><label for="semana-numero">Número de semana</label><input id="semana-numero" name="num_semana" value="{{ old('num_semana') }}" type="number" min="1" class="form-control" required></div>
                    <div class="form-group col-md-4"><label for="semana-nombre">Nombre</label><input id="semana-nombre" name="nombre" value="{{ old('nombre') }}" class="form-control" required></div>
                    <div class="form-group col-md-4"><label for="semana-pat">Programa de acción tutorial</label><select id="semana-pat" name="id_pat" class="form-control">@foreach($pats as $item)<option value="{{ $item->id }}" @selected((string) old('id_pat') === (string) $item->id)>{{ $item->nombre }}</option>@endforeach</select></div>
                    <div class="form-group col-md-2"><label for="semana-tipo">Tipo de tutoría</label><input id="semana-tipo" name="tipo_tutoria" value="{{ old('tipo_tutoria') }}" type="number" class="form-control" required></div>
                </div>
                <div class="form-row">
                    @foreach(['tematica' => 'Temática', 'objetivos' => 'Objetivos', 'justificacion' => 'Justificación', 'estrategias_tutor' => 'Estrategias del tutor', 'acciones' => 'Acciones', 'estrategias_tutorado' => 'Estrategias del tutorado'] as $field => $label)
                        <div class="form-group col-md-6"><label for="semana-{{ $field }}">{{ $label }}{{ $field === 'estrategias_tutorado' ? ' (opcional)' : '' }}</label><textarea id="semana-{{ $field }}" name="{{ $field }}" class="form-control" @required($field !== 'estrategias_tutorado')>{{ old($field) }}</textarea></div>
                    @endforeach
                </div>
            @else
                <div class="form-row">
                    <div class="form-group col-md-6"><label for="desempeno-grupo">Grupo</label><select id="desempeno-grupo" name="id_grupo" class="form-control">@foreach($groups as $item)<option value="{{ $item->id }}" @selected((string) old('id_grupo') === (string) $item->id)>{{ $item->nombre }}</option>@endforeach</select></div>
                    @foreach(['eDesempeño' => 'Excelente', 'bDesempeño' => 'Bueno', 'arDesempeño' => 'Alto riesgo'] as $field => $label)
                        <div class="form-group col-md-2"><label for="desempeno-{{ $loop->index }}">{{ $label }}</label><input id="desempeno-{{ $loop->index }}" name="{{ $field }}" value="{{ old($field) }}" type="number" min="0" class="form-control" required></div>
                    @endforeach
                </div>
                <div class="form-group"><label for="desempeno-comentarios">Comentarios</label><textarea id="desempeno-comentarios" name="comentarios" class="form-control" placeholder="Agrega contexto sobre el desempeño del grupo.">{{ old('comentarios') }}</textarea></div>
            @endif
            <div class="ui-form-actions"><button type="submit" class="btn btn-primary"><i class="fas fa-save" aria-hidden="true"></i> {{ ['pat' => 'Crear PAT', 'semana' => 'Crear semana', 'performance' => 'Crear desempeño'][$kind] }}</button></div>
        </div>
    </form>
    <x-ui.list-panel :title="$title" :paginator="$items">
        <table class="table ui-table">
            <thead><tr>
                @foreach(['pat' => ['Nombre', 'Semestre', 'Estado'], 'semana' => ['Número', 'Nombre', 'PAT'], 'performance' => ['Grupo', 'Excelente', 'Bueno', 'Alto riesgo']][$kind] as $column)
                    <th scope="col">{{ $column }}</th>
                @endforeach
                <th scope="col" class="text-right">Acciones</th>
            </tr></thead>
            <tbody>
                @forelse($items as $item)
                    <tr>
                        @if($kind === 'pat')
                            <td><strong>{{ $item->nombre }}</strong></td><td>{{ $item->semestre?->nombre }}</td><td><x-ui.status-badge :status="$item->estatus ? 'active' : 'inactive'" :label="$item->estatus ? 'Activo' : 'Inactivo'" /></td>
                        @elseif($kind === 'semana')
                            <td>{{ $item->num_semana }}</td><td><strong>{{ $item->nombre }}</strong></td><td>{{ $item->pat?->nombre }}</td>
                        @else
                            <td><strong>{{ $item->grupo?->nombre }}</strong></td><td>{{ $item->eDesempeño }}</td><td>{{ $item->bDesempeño }}</td><td>{{ $item->arDesempeño }}</td>
                        @endif
                        <td><div class="ui-inline-actions"><form method="POST" action="{{ url('/admin/'.['pat' => 'pat', 'semana' => 'semanas', 'performance' => 'performance'][$kind].'/'.$item->id.'/delete') }}" onsubmit="return confirm('¿Eliminar este registro?')">@csrf<button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash-alt" aria-hidden="true"></i> Eliminar</button></form></div></td>
                    </tr>
                @empty
                    <tr><td colspan="{{ $kind === 'performance' ? 5 : 4 }}" class="ui-empty-state"><strong>Aún no hay registros</strong>Utiliza el formulario para agregar el primero.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-ui.list-panel>
</div>
@endsection
