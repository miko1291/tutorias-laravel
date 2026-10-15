@extends('layouts.app')
@section('title', 'Mi inicio · Tutorías')
@php
    $roles = auth()->user()->roles();
    $esAlumno = in_array('alumno', $roles, true);
    $esPsicologia = !$esAlumno && (bool) array_intersect(['psicologo', 'psicologa'], $roles);
    $esTutor = !$esAlumno && !$esPsicologia && in_array('tutor', $roles, true);
    $enviado = $expediente && in_array($expediente->estatus, ['enviado', 'aprobado'], true);
    $perfilManual = $esAlumno ? 'alumno' : ($esTutor ? 'tutor' : ($esPsicologia ? 'psicologo' : 'alumno'));
@endphp
@section('content')
<div class="portal-page">
    @if($esAlumno) @include('site._student-carousel') @endif
    <header class="portal-hero"><div><span class="portal-kicker">{{ $esAlumno ? 'Espacio del estudiante' : ($esTutor ? 'Espacio de tutoría' : ($esPsicologia ? 'Espacio de psicología' : 'Programa Institucional de Tutorías')) }}</span><h1>Hola, {{ $esAlumno && $alumno ? $alumno->nombres : ($esTutor && $tutor ? $tutor->nombre : auth()->user()->username) }}</h1><p>{{ $esAlumno ? 'Tu información, tus avisos y tu acompañamiento académico, en un solo lugar.' : ($esTutor ? 'Organiza el seguimiento de tus grupos y encuentra las tareas que necesitan tu atención.' : 'Consulta tus herramientas de trabajo y acompaña a la comunidad estudiantil.') }}</p></div>
    @if($esAlumno)<a class="portal-primary" href="{{ url($enviado ? '/expediente/ficha' : '/expediente/index') }}">{{ $enviado ? 'Ver mi expediente' : 'Completar mi expediente' }} <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
    @elseif($esTutor)<a class="portal-primary" href="{{ url($grupoActual ? '/grupo-master/index' : '/select-grupo/index') }}">{{ $grupoActual ? 'Abrir mi grupo' : 'Seleccionar grupo' }} <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
    @elseif($esPsicologia)<a class="portal-primary" href="{{ url('/expediente-pregunta/index') }}">Diseñar expediente <i class="bi bi-arrow-right" aria-hidden="true"></i></a>@endif
    </header>
    @if($esAlumno)
        <section class="portal-state {{ $enviado ? '' : 'is-pending' }}" aria-label="Estado de tu expediente"><i class="bi bi-{{ $enviado ? 'check-circle' : 'clipboard' }}" aria-hidden="true"></i><div><strong>{{ $enviado ? 'Tu expediente está enviado' : 'Tu expediente está pendiente de envío' }}</strong><p>{{ $enviado ? 'Puedes consultar tus respuestas y actualizar tu información cuando lo necesites.' : 'Completa las secciones y pulsa Enviar expediente al terminar. Llenar los campos no equivale a enviarlos.' }}</p>@if($expediente)<small>Estado registrado: {{ $expediente->estatus }}</small>@endif</div></section>
    @elseif($esTutor)
        <section class="portal-metrics" aria-label="Resumen de mis grupos"><div class="portal-metric"><i class="bi bi-collection" aria-hidden="true"></i><strong>{{ $grupos->count() }}</strong><span>Grupos asignados</span></div><div class="portal-metric"><i class="bi bi-people" aria-hidden="true"></i><strong>{{ $grupos->sum('alumnos_count') }}</strong><span>Alumnos en mis grupos</span></div><a class="portal-metric" href="{{ url('/grupo-master/pendientes') }}"><i class="bi bi-file-earmark-text" aria-hidden="true"></i><strong>{{ $pendientesTutor }}</strong><span>Expedientes sin enviar →</span></a></section>
        <section class="portal-state {{ $grupoActual ? '' : 'is-pending' }}"><i class="bi bi-collection" aria-hidden="true"></i><div><strong>{{ $grupoActual ? 'Grupo de trabajo: '.$grupoActual->nombre : 'Selecciona tu grupo de trabajo' }}</strong><p>{{ $grupoActual ? 'Las herramientas de seguimiento usarán el grupo seleccionado. Puedes cambiarlo desde Mis grupos.' : ($grupos->isEmpty() ? 'Aún no tienes grupos asignados. Solicita a la coordinación que revise tu asignación.' : 'Elige uno de tus grupos para comenzar. Puedes consultar los expedientes pendientes de todos tus grupos desde el resumen.') }}</p></div></section>
    @endif
    <div class="portal-content-grid"><section><div class="portal-section-heading"><h2>¿Qué necesitas hacer?</h2><small>Accesos a tus actividades</small></div><div class="portal-cards">
    @if($esAlumno)
        <x-ui.portal-card title="Actualizar expediente" description="Responde por secciones y envía tu información." href="/expediente/index" icon="pencil-square" />
        <x-ui.portal-card title="Mi expediente" description="Consulta la ficha con tus respuestas enviadas." href="/expediente/ficha" icon="folder2-open" />
        <x-ui.portal-card title="Mi perfil" description="Revisa tus datos personales y académicos." href="/expediente/perfil" icon="person" />
    @elseif($esTutor)
        <x-ui.portal-card title="Mis grupos" description="Elige o cambia el grupo con el que vas a trabajar." href="/select-grupo/index" icon="collection" />
        <x-ui.portal-card title="Expedientes pendientes" description="Identifica quiénes aún no han enviado su expediente." href="/grupo-master/pendientes" icon="file-earmark-text" />
        <x-ui.portal-card title="Mi grupo" description="Consulta a tus estudiantes y sus fichas de expediente." :href="$grupoActual ? '/grupo-master/index' : '/select-grupo/index'" icon="people" />
        <x-ui.portal-card title="Asistencia" description="Registra asistencias y consulta las listas por fecha." :href="$grupos->isNotEmpty() ? '/asistencia/index' : '/select-grupo/index'" icon="calendar-check" />
        <x-ui.portal-card title="Diagnóstico" description="Registra las necesidades de atención de tus alumnos." :href="$grupoActual ? '/diagnostico/index' : '/select-grupo/index'" icon="clipboard-pulse" />
        <x-ui.portal-card title="Programa tutorial" description="Consulta las actividades y los objetivos del programa." :href="$grupoActual ? '/pat/index' : '/select-grupo/index'" icon="journal-bookmark" />
        <x-ui.portal-card title="Semanas realizadas" description="Consulta y corrige los avances semanales registrados." href="/semana-real/index" icon="calendar-week" />
        <x-ui.portal-card title="Liberación" description="Consulta y registra la evaluación tutorial del alumno." :href="$grupoActual ? '/evaluacion/index' : '/select-grupo/index'" icon="patch-check" />
        <x-ui.portal-card title="Contacto y canalización" description="Registra situaciones que requieren atención o apoyo." :href="$grupoActual ? '/canalizacion/index' : '/select-grupo/index'" icon="chat-square-heart" />
    @elseif($esPsicologia)
        <x-ui.portal-card title="Diseñar expediente" description="Organiza las preguntas, secciones y condiciones del formulario." href="/expediente-pregunta/index" icon="ui-checks" />
        @if(in_array('psicologo', $roles, true))<x-ui.portal-card title="Seguimiento de asistencia" description="Consulta grupos, listas y reportes de asistencia." href="/asistencia/index" icon="calendar-check" /><x-ui.portal-card title="Diagnóstico" description="Consulta y registra las necesidades de atención." href="/diagnostico/index" icon="clipboard-pulse" /><x-ui.portal-card title="Contacto y canalización" description="Consulta las situaciones registradas para acompañamiento." href="/canalizacion/index" icon="chat-square-heart" />@endif
    @endif
        <x-ui.portal-card title="Notificaciones" description="Lee los avisos y abre la actividad relacionada." href="/notification/index" icon="bell" />
    </div></section>
    <aside class="portal-support">
        @if($esTutor)<section class="portal-panel"><h2>Mis grupos</h2>@forelse($grupos as $grupo)<div class="portal-group"><strong>{{ $grupo->nombre ?: 'Grupo '.$grupo->id }}</strong><small>{{ $grupo->alumnos_count }} alumnos {{ $grupoActual?->id === $grupo->id ? '· Seleccionado' : '' }}</small><a href="{{ url('/site/assign-id-grupo/'.$grupo->id) }}">Trabajar con este grupo →</a></div>@empty<p>La coordinación puede ayudarte a revisar la asignación de grupos.</p>@endforelse</section>@endif
        <section class="portal-panel"><span class="portal-kicker">Ayuda para tu día a día</span><h2>Tu manual de usuario</h2><p>Encuentra los pasos para cada actividad, qué significa cada estado y cómo resolver las dudas más comunes.</p><a class="portal-secondary" href="{{ url('/manual?perfil='.$perfilManual) }}">Consultar manual <i class="bi bi-book" aria-hidden="true"></i></a></section>
        <section class="portal-panel"><h2>Acompañamiento cercano</h2><p>La tutoría te ayuda a identificar necesidades, orientar decisiones y fortalecer tu trayectoria. Si necesitas apoyo, acércate a tu tutora, tutor o coordinación.</p></section>
    </aside></div>
    <footer class="portal-bottom"><span>Programa Institucional de Tutorías · ITSVA</span><span>Tu información merece un uso responsable.</span></footer>
</div>
@endsection
@push('styles') @include('partials.portal-styles') @endpush
@if($esAlumno)
    @push('styles')<link rel="stylesheet" href="{{ asset('css/student-carousel.css') }}?v={{ filemtime(public_path('css/student-carousel.css')) }}">@endpush
    @push('scripts')<script src="{{ asset('js/student-carousel.js') }}?v={{ filemtime(public_path('js/student-carousel.js')) }}"></script>@endpush
@endif
