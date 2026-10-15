@extends('layouts.app')

@php
    /** @var \App\Models\Alumno $model */
    $titulo = $model->nombres . ' ' . $model->apellidop . ' ' . $model->apellidom;
@endphp

@section('title', $titulo)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/grupo-master/index') }}">VER GRUPO</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $titulo }}</li>
@endsection

@section('content')
<div class="border rounded">
    <div class="jumbotron jumbotron-fluid pt-3 pb-3 pl-4 pr-4 bg-light form-header">
        <h1 class="display-6 text-black-50 text-uppercase form-tittle">{{ $titulo }}</h1>
        <p class="lead">Información del alumno</p>
    </div>
    <div class="alumno-view m-3">
        <table class="table table-sm">
            <tbody>
                <tr><th>Nombres</th><td>{{ $model->nombres }}</td></tr>
                <tr><th>Apellido paterno</th><td>{{ $model->apellidop }}</td></tr>
                <tr><th>Apellido materno</th><td>{{ $model->apellidom }}</td></tr>
                <tr><th>Matrícula</th><td>{{ $model->matricula }}</td></tr>
                <tr><th>Correo</th><td>{{ $model->correo }}</td></tr>
                <tr><th>Teléfono</th><td>{{ $model->telefono }}</td></tr>
                <tr><th>Fecha de nacimiento</th><td>{{ $model->fecha_nac }}</td></tr>
                <tr><th>Ciudad</th><td>{{ $model->ciudad }}</td></tr>
                <tr><th>Género</th><td>{{ $model->genero != 0 ? 'FEMENINO' : 'MASCULINO' }}</td></tr>
                <tr><th>Registrado</th><td>{{ $model->created_at }}</td></tr>
                <tr><th>Última actualización</th><td>{{ $model->updated_at }}</td></tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
