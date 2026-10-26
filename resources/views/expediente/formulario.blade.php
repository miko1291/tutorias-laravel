@extends('layouts.app')
@section('title', 'Actualizar expediente')
@section('breadcrumbs')<li class="breadcrumb-item active" aria-current="page">Actualizar expediente</li>@endsection
@section('content')
<div class="expediente-update" id="expedienteUpdate">
    <header class="update-heading"><div><a class="update-back" href="{{ url('/site/index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Mi inicio</a><span class="portal-kicker">Mi información tutorial</span><h1>Actualizar expediente</h1><p>Completa una sección a la vez. Al terminar, envía tus respuestas.</p></div><a class="portal-secondary" href="{{ url('/manual?perfil=alumno#actualizar') }}"><i class="bi bi-question-circle" aria-hidden="true"></i> Cómo completar mi expediente</a></header>
    <nav class="update-tabs" aria-label="Mi espacio de estudiante"><a href="{{ url('/expediente/perfil') }}"><i class="bi bi-person" aria-hidden="true"></i> Mi perfil</a><a href="{{ url('/expediente/ficha') }}"><i class="bi bi-folder2-open" aria-hidden="true"></i> Mi expediente</a><a href="{{ url('/expediente/index') }}" aria-current="page"><i class="bi bi-pencil-square" aria-hidden="true"></i> Actualizar expediente</a></nav>
    <div class="update-layout ef-document">
        <aside class="update-sidebar"><div class="update-student"><span class="portal-kicker">Estudiante</span><strong>{{ $alumno->nombre_completo }}</strong><small>Matrícula {{ $alumno->matricula }}</small></div><div class="update-progress"><strong>Tu progreso</strong><span id="formProgressLabel" aria-live="polite">Preguntas obligatorias</span><div class="ef-progress-track" role="progressbar" aria-label="Preguntas obligatorias completas" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><span id="formProgressBar" style="width:0"></span></div></div><nav class="update-section-nav" aria-label="Secciones del expediente">@foreach($preguntasPersonalizadas->groupBy('seccion') as $seccion => $items)<a href="#section-{{ $loop->index }}" data-step-link="{{ $loop->index }}"><span>{{ $loop->iteration }}</span><strong>{{ $seccion }}</strong><i class="bi bi-check2" aria-hidden="true" hidden></i></a>@endforeach</nav><a class="update-guide-link" href="{{ url('/manual?perfil=alumno#actualizar') }}"><i class="bi bi-book" aria-hidden="true"></i> Ayuda con el expediente</a></aside>
        <div class="update-main"><div class="update-notice"><i class="bi bi-shield-check" aria-hidden="true"></i><span>Tu información es confidencial y se utiliza para el acompañamiento educativo. Los campos con <strong>*</strong> son obligatorios.</span></div><p class="update-step-status" id="currentStepLabel" aria-live="polite"></p>
        <form id="actualizacionForm" method="post" action="{{ url('/expediente/guardar') }}" data-section-wizard>
            @csrf
            @forelse($preguntasPersonalizadas->groupBy('seccion') as $seccion => $items)
            <section class="ef-section" id="section-{{ $loop->index }}" data-step="{{ $loop->index }}"><div class="ef-section-heading"><span class="portal-kicker">Sección {{ $loop->iteration }}</span><h2 tabindex="-1">{{ $seccion }}</h2><small>{{ $items->count() }} {{ $items->count() === 1 ? 'pregunta' : 'preguntas' }} · Revisa tus respuestas antes de continuar.</small></div>@foreach($items as $pregunta) @include('expediente._campo') @endforeach</section>
            @empty<div class="portal-state is-pending"><div><strong>El formulario todavía no está disponible</strong><p>Consulta con la coordinación de tutorías antes de enviar tu expediente.</p></div></div>@endforelse
            @if($preguntasPersonalizadas->isNotEmpty())
            <div id="updateReview" class="update-review" hidden><i class="bi bi-send-check" aria-hidden="true"></i><div><strong>Revisa tus respuestas</strong><p>Antes de enviar, comprueba que tus datos estén correctos.</p></div></div>
            <div class="update-actions"><button type="button" class="portal-secondary" data-step-previous hidden><i class="bi bi-arrow-left" aria-hidden="true"></i> Anterior</button><small id="stepHint">Las respuestas se guardan al enviar el expediente.</small><button type="button" class="portal-primary" data-step-next hidden>Siguiente sección <i class="bi bi-arrow-right" aria-hidden="true"></i></button><button class="portal-primary" id="sendExpediente" type="submit">Enviar expediente <i class="bi bi-send" aria-hidden="true"></i></button></div>
            @endif
        </form></div>
    </div>
</div>
@endsection
@push('styles')
@include('partials.portal-styles')
<link rel="stylesheet" href="{{ asset('css/expediente-form.css') }}?v={{ filemtime(public_path('css/expediente-form.css')) }}">
<link rel="stylesheet" href="{{ asset('css/expediente-update.css') }}?v={{ filemtime(public_path('css/expediente-update.css')) }}">
@endpush
@push('scripts')
<script src="{{ asset('js/expediente-form.js') }}?v={{ filemtime(public_path('js/expediente-form.js')) }}"></script>
<script src="{{ asset('js/expediente-wizard.js') }}?v={{ filemtime(public_path('js/expediente-wizard.js')) }}"></script>
@endpush
