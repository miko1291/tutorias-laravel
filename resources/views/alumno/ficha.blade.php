@extends('layouts.app')

@section('title', 'Ficha de expediente · ' . $alumno->nombre_completo)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/grupo-master/index') }}">Grupo</a></li>
    <li class="breadcrumb-item active" aria-current="page">Ficha de expediente · {{ $alumno->nombre_completo }}</li>
@endsection

@section('content')
    @include('expediente._ficha', [
        'alumno' => $alumno,
        'expediente' => $expediente,
        'respuestas' => $respuestas,
        'allowEdit' => $allowEdit,
    ])
@endsection
