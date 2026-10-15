@extends('layouts.app')
@section('title', 'Panel administrativo')
@section('breadcrumbs')<li class="breadcrumb-item active" aria-current="page">Panel administrativo</li>@endsection
@section('content')
<div class="portal-page">
    <header class="portal-hero"><div><span class="portal-kicker">Administración · Programa de Tutorías</span><h1>Panel administrativo</h1><p>Organiza la comunidad, prepara el programa tutorial y da seguimiento al envío de expedientes.</p></div><a class="portal-primary" href="{{ url('/grupo-master/pendientes') }}">Revisar pendientes <i class="bi bi-arrow-right" aria-hidden="true"></i></a></header>
    <section class="portal-metrics" aria-label="Resumen institucional">
    @foreach([['Usuarios', $usersCount, 'person-gear', '/admin/users'], ['Alumnos', $studentsCount, 'mortarboard', '/alumno/index'], ['Tutores', $tutorsCount, 'person-workspace', '/admin/tutors'], ['Grupos', $groupsCount, 'collection', '/admin/groups'], ['Expedientes sin enviar', $pendingExpedientes, 'file-earmark-text', '/grupo-master/pendientes']] as [$title, $count, $icon, $href])
        <a class="portal-metric" href="{{ url($href) }}"><i class="bi bi-{{ $icon }}" aria-hidden="true"></i><strong>{{ $count }}</strong><span>{{ $title }} →</span></a>
    @endforeach
    </section>
    <div class="portal-content-grid"><section><div class="portal-section-heading"><h2>Gestiona el programa</h2><small>Herramientas de administración</small></div><div class="portal-cards">
        <x-ui.portal-card title="Usuarios y roles" description="Gestiona las cuentas, sus permisos y su estado de acceso." href="/admin/users" icon="person-gear" />
        <x-ui.portal-card title="Alumnos" description="Consulta y organiza la información de los estudiantes." href="/alumno/index" icon="mortarboard" />
        <x-ui.portal-card title="Tutores" description="Relaciona a cada tutora o tutor con su cuenta." href="/admin/tutors" icon="person-workspace" />
        <x-ui.portal-card title="Grupos" description="Organiza los grupos y asigna tutores y estudiantes." href="/admin/groups" icon="collection" />
        <x-ui.portal-card title="Diseñar expediente" description="Edita el formulario que responderán los estudiantes." href="/expediente-pregunta/index" icon="ui-checks" />
        <x-ui.portal-card title="Expedientes pendientes" description="Consulta quiénes aún no han enviado su información." href="/grupo-master/pendientes" icon="file-earmark-text" />
        <x-ui.portal-card title="Programa tutorial" description="Organiza el PAT, las semanas y los desempeños." href="/admin/pat" icon="journal-bookmark" />
        <x-ui.portal-card title="Catálogos" description="Mantén al día carreras, semestres y periodos escolares." href="/admin/catalogos/carreras" icon="list-ul" />
    </div></section><aside class="portal-support"><section class="portal-panel"><span class="portal-kicker">Primeros pasos</span><h2>Prepara el ciclo</h2><p>Revisa los catálogos, las cuentas y la asignación de grupos. Después verifica el formulario y consulta los expedientes pendientes.</p><a class="portal-secondary" href="{{ url('/admin/groups') }}">Revisar grupos</a></section><section class="portal-panel"><h2>Manual de administración</h2><p>Consulta cómo gestionar cuentas, grupos, catálogos y el diseñador de expedientes, paso a paso.</p><a class="portal-secondary" href="{{ url('/manual?perfil=admin') }}">Consultar manual <i class="bi bi-book" aria-hidden="true"></i></a></section><section class="portal-panel"><h2>Avisos del sistema</h2><p>Revisa tus notificaciones y abre la actividad relacionada.</p><a class="portal-secondary" href="{{ url('/notification/index') }}">Ver notificaciones</a></section></aside></div>
</div>
@endsection
@push('styles') @include('partials.portal-styles') @endpush
