@extends('layouts.app')

@section('title', 'Nuevo alumno')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/alumno/index') }}">Alumnos</a></li>
    <li class="breadcrumb-item active" aria-current="page">Nuevo alumno</li>
@endsection

@section('content')
<div class="alumno-create ui-resource-page">

    <x-ui.page-header eyebrow="Gestión académica" title="Nuevo alumno" description="Completa los datos del alumno." />

    @include('alumno._form', [
        'model' => $model,
        'accion' => url('/alumno/create/' . $id_grupo),
    ])

</div>
@endsection
