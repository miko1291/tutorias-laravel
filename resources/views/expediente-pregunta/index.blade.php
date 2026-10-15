@extends('layouts.app')
@section('title', 'Diseñar expediente')
@section('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">Diseñar expediente</li>
@endsection
@section('content')
<div class="df-workspace" id="expedienteDesigner" data-save-url="{{ url('/expediente-pregunta/diseno') }}">
    <div class="df-topbar">
        <div><span class="df-kicker">Formulario institucional</span><h1>Diseñar expediente</h1><p id="designStatus" role="status" aria-live="polite">Diseño guardado</p></div>
        <div class="df-top-actions">
            <button type="button" class="btn btn-outline-secondary" data-action="undo" title="Deshacer" aria-label="Deshacer"><i class="bi bi-arrow-counterclockwise"></i></button>
            <button type="button" class="btn btn-outline-secondary" data-action="redo" title="Rehacer" aria-label="Rehacer"><i class="bi bi-arrow-clockwise"></i></button>
            <button type="button" class="btn btn-outline-primary" data-action="preview"><i class="bi bi-eye"></i> <span id="previewLabel">Vista de alumno</span></button>
            <button type="button" class="btn btn-primary" id="saveDesign" data-action="save"><i class="bi bi-check2"></i> Guardar diseño</button>
        </div>
    </div>
    <div id="designError" class="alert alert-danger" role="alert" hidden></div>
    <div class="df-toolbar" id="designTools">
        <div><button type="button" data-action="add-question"><i class="bi bi-plus-circle"></i> Pregunta</button><button type="button" data-action="add-section"><i class="bi bi-layout-text-window-reverse"></i> Sección</button></div>
        <div><button type="button" data-action="archive"><i class="bi bi-archive"></i> Archivadas <span id="archiveCount">0</span></button><button type="button" data-action="tools"><i class="bi bi-sliders"></i> Herramientas</button></div>
    </div>
    <div id="previewNotice" class="alert alert-info" hidden>Vista de alumno. Puedes probar las respuestas y condiciones; estos datos de prueba no se guardan.</div>
    <div class="df-grid">
        <aside class="df-outline" aria-label="Secciones del formulario"><strong>En este expediente</strong><nav id="sectionOutline"></nav><small>Arrastra las preguntas por el asa o usa las flechas para moverlas.</small></aside>
        <div class="ef-document df-document" id="designDocument"></div>
        <aside class="df-inspector" id="questionInspector" aria-label="Herramientas de pregunta"></aside>
    </div>
    <dialog id="archiveDialog" class="df-dialog"><div class="d-flex justify-content-between align-items-center mb-3"><h2>Preguntas archivadas</h2><button type="button" class="btn btn-light" data-action="close-archive" aria-label="Cerrar archivadas">×</button></div><p class="text-muted">Puedes recuperarlas. Sus respuestas anteriores se conservan.</p><div id="archiveList"></div></dialog>
</div>
@endsection
@push('styles')
<link rel="stylesheet" href="{{ asset('css/expediente-form.css') }}?v={{ filemtime(public_path('css/expediente-form.css')) }}">
<link rel="stylesheet" href="{{ asset('css/question-builder.css') }}?v={{ filemtime(public_path('css/question-builder.css')) }}">
@endpush
@push('scripts')
<script>window.expedienteDesign = {{ Illuminate\Support\Js::from($designerData) }};</script>
<script src="{{ asset('js/expediente-form.js') }}?v={{ filemtime(public_path('js/expediente-form.js')) }}"></script>
<script src="{{ asset('js/question-builder.js') }}?v={{ filemtime(public_path('js/question-builder.js')) }}"></script>
@endpush
