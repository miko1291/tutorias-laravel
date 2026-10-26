@extends('layouts.blank')
@section('title', 'Bienvenido a Tutorías')
@section('access-landing', true)
@section('hide-carousel', true)
@section('content')
<div class="auth-page">
    @include('site._brand')
    <div class="auth-layout">
        <section class="auth-story"><span class="portal-kicker">Programa Institucional de Tutorías</span><h1>Sistema de<br>tutorías</h1><p>Consulta tu expediente, revisa los avisos y da seguimiento a tus actividades de tutoría.</p><div class="auth-photo"><img src="{{ asset('images/tuto2.png') }}" alt="Comunidad estudiantil del ITSVA"><span>Instituto Tecnológico Superior de Valladolid</span></div></section>
        <section class="auth-panel" aria-labelledby="access-title"><h2 id="access-title">Bienvenido a Tutorías</h2><p>Elige tu perfil para continuar con tus actividades.</p><div class="auth-roles">
        @foreach([
            ['alumno', 'Alumno', 'Mi perfil, expediente y avisos.', 'mortarboard'],
            ['tutor', 'Tutora o tutor', 'Grupos, asistencia y seguimiento.', 'people'],
            ['admin', 'Administrador', 'Organización y gestión del programa.', 'grid'],
        ] as [$role, $title, $description, $icon])
            <a class="auth-role" href="{{ url('/site/login?perfil='.$role) }}"><span class="portal-card-icon"><i class="bi bi-{{ $icon }}" aria-hidden="true"></i></span><span><strong>{{ $title }}</strong><small>{{ $description }}</small></span><i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        @endforeach
        </div><div class="auth-help">¿Es tu primera visita? <a href="{{ url('/manual') }}">Consulta los manuales de usuario</a>.<br>Si necesitas una cuenta, solicita apoyo a la coordinación de tutorías.</div></section>
    </div>
</div>
@endsection
@push('styles') @include('partials.portal-styles') @endpush
