@extends('layouts.app')

@section('title', 'Importar alumnos desde Excel')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/alumno/index') }}">Alumnos</a></li>
    <li class="breadcrumb-item active" aria-current="page">Importar Excel</li>
@endsection

@section('content')
<div class="alumno-importar ui-resource-page">
    <x-ui.page-header eyebrow="Gestión de alumnos" title="Importar alumnos desde Excel" description="Registra varios alumnos en una sola carga."><a class="btn btn-outline-primary" href="{{ url('/alumno/index') }}"><i class="fas fa-arrow-left" aria-hidden="true"></i> Volver a alumnos</a></x-ui.page-header>
    @if(session('import_success'))
        <div class="alert alert-success" role="status">{{ session('import_success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong>No se importó ningún alumno. Corrige los errores y vuelve a subir el archivo.</strong>
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            <small>Se muestran hasta 100 errores por intento.</small>
        </div>
    @endif
    <div class="ui-import-layout">
    <div class="card ui-form-card">
        <div class="card-body">
            <h2 class="ui-form-card__title">1. Prepara tu archivo</h2>
            <p><a class="btn btn-outline-primary" href="{{ route('alumno.plantilla') }}">Descargar plantilla Excel</a></p>
            <p>Reemplaza la fila de ejemplo con tus alumnos. Conserva los encabezados de la primera fila y usa la primera hoja del archivo.</p>
            <p>Todos los campos son obligatorios: nombres, apellidop, apellidom, matricula, correo, telefono, fecha_nac, ciudad y genero.</p>
            <ul>
                <li>Fecha de nacimiento: AAAA-MM-DD (ejemplo: 2005-03-15) o una fecha de Excel.</li>
                <li>Género: Masculino o 0; Femenino o 1.</li>
                <li>Escribe matrícula y teléfono como texto para conservar los ceros iniciales.</li>
                <li>Las matrículas deben ser únicas. Los alumnos existentes no se actualizan.</li>
            </ul>
            <p class="mb-0">Se aceptan .xlsx, .xls y .csv, hasta 5 MB y 2,000 alumnos por archivo.</p>
        </div>
    </div>
    <div class="card ui-form-card">
        <div class="card-body">
            <h2 class="ui-form-card__title">2. Selecciona el grupo y sube el archivo</h2>
            <form method="post" action="{{ route('alumno.importar.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="id_grupo">Grupo para todos los alumnos del archivo</label>
                    <select name="id_grupo" id="id_grupo" class="form-control">
                        <option value="">Sin grupo asignado</option>
                        @foreach($grupos as $grupo)
                            <option value="{{ $grupo->id }}" @selected((string) old('id_grupo', request('id_grupo')) === (string) $grupo->id)>{{ $grupo->nombre }} — {{ $grupo->periodo?->nombre }} (ID {{ $grupo->id }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group ui-upload">
                    <label for="archivo">Archivo de alumnos</label>
                    <input type="file" name="archivo" id="archivo" class="form-control-file" accept=".xlsx,.xls,.csv" required aria-describedby="archivo-ayuda">
                    <small id="archivo-ayuda" class="form-text text-muted">Se revisan todas las filas antes de guardar. Si hay errores, no se guarda ningún alumno.</small>
                </div>
                <div class="ui-form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-file-import" aria-hidden="true"></i> Importar alumnos</button>
                    <a class="btn btn-outline-secondary" href="{{ url('/alumno/index') }}">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
    </div>
</div>
@endsection
