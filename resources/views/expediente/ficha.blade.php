@extends('layouts.app')

@section('title', 'Mi ficha de expediente')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Mi ficha de expediente</li>
@endsection

@section('content')
<div class="student-area-page">
    @include('expediente._student-header', [
        'activePage' => 'ficha',
        'pageTitle' => 'Mi expediente',
        'pageDescription' => 'Consulta tus respuestas y la información de tu seguimiento tutorial.',
    ])
    @if ($expediente)
        <div class="student-record-summary">
            <div><small>Estado del expediente</small><span class="student-status {{ in_array($expediente->estatus, ['enviado', 'aprobado'], true) ? 'student-status--sent' : 'student-status--pending' }}">{{ ucfirst((string) $expediente->estatus) }}</span></div>
            <div><small>Última actualización</small><strong>{{ $expediente->updated_at?->format('d/m/Y · H:i') ?: 'Sin registro' }}</strong></div>
            <div><small>Versión registrada</small><strong>{{ (int) $expediente->version }}</strong></div>
            <a class="student-button student-button--secondary" href="{{ url('/expediente/index') }}"><i class="bi bi-pencil-square" aria-hidden="true"></i> Actualizar</a>
        </div>
    @include('expediente._ficha', [
        'alumno' => $alumno,
        'expediente' => $expediente,
        'respuestas' => $respuestas,
        'allowEdit' => $allowEdit,
    ])
    @else
        <div class="student-empty">
            <i class="bi bi-folder2-open" aria-hidden="true"></i>
            <h2>Tu expediente comienza aquí</h2>
            <p>Aún no tienes un expediente registrado. Completa tu información para iniciar tu seguimiento tutorial.</p>
            <a class="student-button" href="{{ url('/expediente/index') }}"><i class="bi bi-plus-circle" aria-hidden="true"></i> Completar mi expediente</a>
        </div>
    @endif
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/student-pages.css') }}?v=20261005">
@endpush
