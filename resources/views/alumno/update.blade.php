@extends('layouts.app')

@section('title', 'Editar alumno: ' . $model->id)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/alumno/index') }}">Alumnos</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/alumno/view/' . $model->id) }}">{{ $model->id }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Editar</li>
@endsection

@section('content')
<div class="alumno-update">

    <x-ui.page-header eyebrow="Gestión académica" :title="'Editar alumno: '.$model->nombre_completo" description="Actualiza la información registrada del estudiante." />

    @include('alumno._form', [
        'model' => $model,
        'accion' => url('/alumno/update/' . $model->id),
    ])

</div>
@endsection
