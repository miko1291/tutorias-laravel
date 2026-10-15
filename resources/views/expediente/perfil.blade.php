@extends('layouts.app')

@php
    /** @var \App\Models\Alumno $alumno */
    /** @var \App\Models\AlumnoExpediente|null $expediente */
    $respuestasPerfil = $expediente ? $expediente->getRespuestas() : [];
    $cleanValue = static function ($value) {
        $value = trim((string) $value);
        return $value !== '' && $value !== '.' && $value !== '-' ? $value : '';
    };
    $fechaNacimiento = $cleanValue($respuestasPerfil['fecha_nacimiento'] ?? $alumno->fecha_nac ?? '');
    $ciudad = $cleanValue($respuestasPerfil['campo_013'] ?? '');
    if ($ciudad === '') $ciudad = $cleanValue($respuestasPerfil['campo_012'] ?? '');
    if ($ciudad === '') $ciudad = $cleanValue($alumno->ciudad ?? '');
    $grupo = $alumno->grupo;
    $grupoDescripcion = $grupo ? trim(implode(' · ', array_filter([
        $grupo->carrera->nombre ?? null,
        $grupo->semestre->nombre ?? null,
        $grupo->grupoLetra->letra_key ?? null,
    ]))) : '';
    $edad = null;
    if ($fechaNacimiento && ($fecha = \DateTimeImmutable::createFromFormat('!Y-m-d', $fechaNacimiento))) {
        $edad = $fecha->diff(new \DateTimeImmutable('today'))->y;
    }
@endphp

@section('title', 'Mi perfil')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Mi perfil</li>
@endsection

@section('content')
<div class="student-area-page">
    @include('expediente._student-header', [
        'activePage' => 'perfil',
        'pageTitle' => 'Mi perfil',
        'pageDescription' => 'Tu información personal y académica, en un solo lugar.',
    ])

    <section class="student-profile-banner" aria-label="Identificación del estudiante">
        <div class="student-profile-avatar" aria-hidden="true">
            {{ mb_strtoupper(mb_substr($alumno->nombres, 0, 1)) }}
        </div>
        <div class="student-profile-name">
            <h2>{{ $alumno->nombre_completo }}</h2>
            <p>{{ $grupoDescripcion ?: 'Estudiante del Programa Institucional de Tutorías' }}</p>
        </div>
        <span class="student-status {{ in_array($expediente?->estatus, ['enviado', 'aprobado'], true) ? 'student-status--sent' : 'student-status--pending' }}">
            <i class="bi {{ in_array($expediente?->estatus, ['enviado', 'aprobado'], true) ? 'bi-check-circle' : 'bi-clock' }}" aria-hidden="true"></i>
            {{ $expediente ? 'Expediente '.ucfirst((string) $expediente->estatus) : 'Expediente sin enviar' }}
        </span>
    </section>

    <div class="student-profile-grid">
        <section class="student-info-card" aria-labelledby="academic-title">
            <div class="student-card-heading">
                <i class="bi bi-mortarboard" aria-hidden="true"></i>
                <div><h2 id="academic-title">Datos académicos</h2><p>Información de tu registro escolar</p></div>
            </div>
            <dl class="student-data-list">
                <div class="student-data-row"><dt>Matrícula</dt><dd>{{ $alumno->matricula }}</dd></div>
                <div class="student-data-row"><dt>Carrera</dt><dd>{{ $grupo?->carrera?->nombre ?: 'No asignada' }}</dd></div>
                <div class="student-data-row"><dt>Semestre</dt><dd>{{ $grupo?->semestre?->nombre ?: 'No asignado' }}</dd></div>
                <div class="student-data-row"><dt>Grupo</dt><dd>{{ $grupo?->grupoLetra?->letra_key ?: ($grupo?->id ?: 'No asignado') }}</dd></div>
                <div class="student-data-row"><dt>Correo institucional</dt><dd>{{ $alumno->correo ?: 'No registrado' }}</dd></div>
            </dl>
        </section>
        <section class="student-info-card" aria-labelledby="contact-title">
            <div class="student-card-heading">
                <i class="bi bi-person-vcard" aria-hidden="true"></i>
                <div><h2 id="contact-title">Datos de contacto</h2><p>Información personal registrada</p></div>
            </div>
            <dl class="student-data-list">
                <div class="student-data-row"><dt>Teléfono</dt><dd>{{ $alumno->telefono ?: 'No registrado' }}</dd></div>
                <div class="student-data-row"><dt>Ciudad o localidad</dt><dd>{{ $ciudad ?: 'Sin registro' }}</dd></div>
                <div class="student-data-row"><dt>Fecha de nacimiento</dt><dd>{{ $fechaNacimiento ?: 'No registrada' }}</dd></div>
                <div class="student-data-row"><dt>Edad</dt><dd>{{ $edad !== null ? (int) $edad . ' años' : 'Sin registro' }}</dd></div>
                <div class="student-data-row"><dt>Última actualización</dt><dd>{{ $expediente?->updated_at?->format('d/m/Y · H:i') ?: 'Sin actualización' }}</dd></div>
            </dl>
        </section>
    </div>

    <aside class="student-next-step">
        <div><h2>Tu información al día</h2><p>Si alguno de tus datos cambió, revísalo en la actualización de tu expediente.</p></div>
        <a class="student-button" href="{{ url('/expediente/index') }}"><i class="bi bi-pencil-square" aria-hidden="true"></i> Actualizar expediente</a>
    </aside>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/student-pages.css') }}?v=20261005">
@endpush
