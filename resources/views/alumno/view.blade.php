@extends('layouts.app')
@php
    $titulo = $model->nombres.' '.$model->apellidop.' '.$model->apellidom;
@endphp
@section('title', $titulo)
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url(auth()->user()->hasRole('admin') ? '/alumno/index' : '/grupo-master/index') }}">Alumnos</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $titulo }}</li>
@endsection
@section('content')
<div class="ui-resource-page">
    <x-ui.page-header eyebrow="Gestión de alumnos" :title="$titulo" description="Información personal y datos de contacto del estudiante.">
        <a class="btn btn-outline-primary" href="{{ url(auth()->user()->hasRole('admin') ? '/alumno/index' : '/grupo-master/index') }}"><i class="fas fa-arrow-left" aria-hidden="true"></i> Volver</a>
        @if(auth()->user()->hasRole('admin'))
            <a class="btn btn-primary" href="{{ url('/alumno/update/'.$model->id) }}"><i class="fas fa-pencil-alt" aria-hidden="true"></i> Editar alumno</a>
        @endif
    </x-ui.page-header>
    <dl class="ui-summary-grid">
        <div class="ui-summary-item"><dt>Matrícula</dt><dd>{{ $model->matricula }}</dd></div>
        <div class="ui-summary-item"><dt>Correo electrónico</dt><dd>{{ $model->correo ?: 'Sin registrar' }}</dd></div>
        <div class="ui-summary-item"><dt>Teléfono</dt><dd>{{ $model->telefono ?: 'Sin registrar' }}</dd></div>
    </dl>
    <section class="card">
        <div class="ui-panel-heading"><h2>Información del alumno</h2><span class="ui-count">ID {{ $model->id }}</span></div>
        <div class="card-body">
            <dl class="ui-detail-grid">
                @foreach(['nombres' => 'Nombres', 'apellidop' => 'Apellido paterno', 'apellidom' => 'Apellido materno', 'fecha_nac' => 'Fecha de nacimiento', 'ciudad' => 'Ciudad o localidad'] as $campo => $label)
                    <div><dt>{{ $label }}</dt><dd>{{ $model->$campo ?: 'Sin registrar' }}</dd></div>
                @endforeach
                <div><dt>Género</dt><dd>{{ $model->genero != 0 ? 'Femenino' : 'Masculino' }}</dd></div>
            </dl>
            <div class="ui-detail-meta"><span>Registrado: {{ $model->created_at ?: 'Sin fecha' }}</span><span>Última actualización: {{ $model->updated_at ?: 'Sin fecha' }}</span></div>
        </div>
    </section>
</div>
@endsection
