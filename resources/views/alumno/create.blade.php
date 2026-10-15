@extends('layouts.app')

@section('title', 'Nuevo alumno')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/alumno/index') }}">Alumnos</a></li>
    <li class="breadcrumb-item active" aria-current="page">Nuevo alumno</li>
@endsection

@section('content')
<div class="alumno-create">

    <x-ui.page-header eyebrow="Gestión académica" title="Nuevo alumno" description="Registra los datos básicos del estudiante para incorporarlo al programa." />

    @include('alumno._form', [
        'model' => $model,
        'accion' => url('/alumno/create/' . $id_grupo),
    ])

</div>
@endsection
