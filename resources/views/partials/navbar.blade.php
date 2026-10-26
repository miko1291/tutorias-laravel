@php
    $navigationRoles = auth()->user()?->roles() ?? [];
    $unreadNotifications = auth()->check() ? \App\Models\Notification::unreadCount(auth()->id()) : 0;
    $notifBadge = $unreadNotifications ? ' <span class="badge badge-warning">'.$unreadNotifications.'</span>' : '';
@endphp
<nav class="navbar navbar-expand-pit navbar-dark pit-navbar" aria-label="Navegación principal">
    <a class="navbar-brand" href="{{ url('/') }}">{{ config('app.name') }}</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Abrir menú de navegación">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarMain">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item"><a class="nav-link" href="{{ url('/site/index') }}"><i class="bi bi-house-door" aria-hidden="true"></i> INICIO</a></li>
            @if(auth()->check())
                <li class="nav-item"><a class="nav-link" href="{{ url('/notification/index') }}"><i class="bi bi-bell" aria-hidden="true"></i> NOTIFICACIONES{!! $notifBadge !!}</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ url('/manual') }}"><i class="bi bi-book" aria-hidden="true"></i> AYUDA</a></li>
            @endif
            @if(!auth()->check())
                <li class="nav-item"><a class="nav-link" href="{{ $ingresarUrl }}"><i class="bi bi-box-arrow-in-right" aria-hidden="true"></i> INGRESAR</a></li>
            @elseif(in_array('admin', $navigationRoles, true))
                <li class="nav-item"><a class="nav-link" href="{{ url('/admin/index') }}"><i class="bi bi-speedometer2" aria-hidden="true"></i> PANEL</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="{{ url('/admin/users') }}" id="navAdminGestion" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="bi bi-people-fill" aria-hidden="true"></i> GESTIÓN</a>
                    <div class="dropdown-menu" aria-labelledby="navAdminGestion">
                        <a class="dropdown-item" href="{{ url('/admin/users') }}"><i class="bi bi-person-gear" aria-hidden="true"></i> Usuarios y roles</a>
                        <a class="dropdown-item" href="{{ url('/alumno/index') }}"><i class="bi bi-mortarboard" aria-hidden="true"></i> Alumnos</a>
                        <a class="dropdown-item" href="{{ url('/admin/tutors') }}"><i class="bi bi-person-workspace" aria-hidden="true"></i> Tutores</a>
                        <a class="dropdown-item" href="{{ url('/admin/groups') }}"><i class="bi bi-diagram-3" aria-hidden="true"></i> Grupos</a>
                        <a class="dropdown-item" href="{{ url('/expediente-pregunta/index') }}"><i class="bi bi-ui-checks" aria-hidden="true"></i> Diseñar expediente</a>
                        <a class="dropdown-item" href="{{ url('/grupo-master/pendientes') }}"><i class="bi bi-file-earmark-text" aria-hidden="true"></i> Expedientes pendientes</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="{{ url('/admin/pat') }}" id="navAdminPrograma" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="bi bi-bar-chart-line" aria-hidden="true"></i> SEGUIMIENTO</a>
                    <div class="dropdown-menu" aria-labelledby="navAdminPrograma">
                        <a class="dropdown-item" href="{{ url('/admin/pat') }}"><i class="bi bi-journal-text" aria-hidden="true"></i> PAT</a>
                        <a class="dropdown-item" href="{{ url('/admin/semanas') }}"><i class="bi bi-calendar-week" aria-hidden="true"></i> Semanas</a>
                        <a class="dropdown-item" href="{{ url('/admin/performance') }}"><i class="bi bi-graph-up" aria-hidden="true"></i> Desempeño</a>
                        <a class="dropdown-item" href="{{ url('/asistencia/index') }}"><i class="bi bi-calendar-check" aria-hidden="true"></i> Asistencia</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="{{ url('/admin/catalogos/carreras') }}" id="navAdminCatalogos" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="bi bi-list-check" aria-hidden="true"></i> CATÁLOGOS</a>
                    <div class="dropdown-menu" aria-labelledby="navAdminCatalogos">
                        <a class="dropdown-item" href="{{ route('admin.expediente-catalogos') }}"><i class="bi bi-geo-alt" aria-hidden="true"></i> Estados, municipios y listas</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ url('/admin/catalogos/carreras') }}"><i class="bi bi-mortarboard" aria-hidden="true"></i> Carreras</a>
                        <a class="dropdown-item" href="{{ url('/admin/catalogos/semestres') }}"><i class="bi bi-123" aria-hidden="true"></i> Semestres</a>
                        <a class="dropdown-item" href="{{ url('/admin/catalogos/grupo-letra') }}"><i class="bi bi-collection" aria-hidden="true"></i> Letras de grupo</a>
                        <a class="dropdown-item" href="{{ url('/admin/catalogos/estatus') }}"><i class="bi bi-check-circle" aria-hidden="true"></i> Estatus</a>
                        <a class="dropdown-item" href="{{ url('/admin/catalogos/ciclos') }}"><i class="bi bi-calendar3" aria-hidden="true"></i> Ciclos escolares</a>
                        <a class="dropdown-item" href="{{ url('/admin/catalogos/periodos') }}"><i class="bi bi-calendar-range" aria-hidden="true"></i> Periodos escolares</a>
                        <a class="dropdown-item" href="{{ url('/admin/catalogos/criterios') }}"><i class="bi bi-list-check" aria-hidden="true"></i> Criterios</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navAdminConfiguracion" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="bi bi-gear" aria-hidden="true"></i> CONFIGURACIÓN</a>
                    <div class="dropdown-menu" aria-labelledby="navAdminConfiguracion">
                        <a class="dropdown-item" href="{{ url('/manual') }}"><i class="bi bi-book" aria-hidden="true"></i> Manual de usuario</a>
                    </div>
                </li>
                <li>@include('partials.logout-button')</li>
            @elseif(in_array('alumno', $navigationRoles, true))
                <li class="nav-item"><a class="nav-link" href="{{ url('/expediente/perfil') }}"><i class="bi bi-person" aria-hidden="true"></i> MI PERFIL</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ url($expedienteUrl) }}"><i class="bi bi-file-earmark-text" aria-hidden="true"></i> MI EXPEDIENTE</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ url('/expediente/index') }}"><i class="bi bi-pencil-square" aria-hidden="true"></i> ACTUALIZAR EXPEDIENTE</a></li>
                <li>@include('partials.logout-button')</li>
            @elseif(array_intersect(['psicologo', 'psicologa'], $navigationRoles))
                <li class="nav-item"><a class="nav-link" href="{{ url('/expediente-pregunta/index') }}"><i class="bi bi-ui-checks" aria-hidden="true"></i> DISEÑAR EXPEDIENTE</a></li>
                @if(in_array('psicologo', $navigationRoles, true))<li class="nav-item"><a class="nav-link" href="{{ url('/asistencia/index') }}"><i class="bi bi-calendar-check" aria-hidden="true"></i> SEGUIMIENTO</a></li>@endif
                <li>@include('partials.logout-button')</li>
            @elseif(in_array('tutor', $navigationRoles, true))
                @php
                    $tutorNav = auth()->user()->tutor;
                    $gruposCount = $tutorNav ? \App\Models\GrupoMaster::where('id_tutor', $tutorNav->id)->count() : 0;
                    $idGrupoNav = session('id_grupo');
                @endphp
                <li class="nav-item"><a class="nav-link" href="{{ url('/tutor/index') }}"><i class="bi bi-person" aria-hidden="true"></i> PERFIL</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ url('/grupo-master/pendientes') }}"><i class="bi bi-file-earmark-text" aria-hidden="true"></i> EXPEDIENTES PENDIENTES</a></li>
                @if($idGrupoNav === null)
                    <li class="nav-item"><a class="nav-link" href="{{ url('/select-grupo/index') }}"><i class="bi bi-collection" aria-hidden="true"></i> SELECCIONAR GRUPO</a></li>
                @else
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navGestion" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="bi bi-people-fill" aria-hidden="true"></i> GESTIÓN</a>
                        <div class="dropdown-menu" aria-labelledby="navGestion">
                            <a class="dropdown-item" href="{{ url('/grupo-master/index') }}"><i class="bi bi-people" aria-hidden="true"></i> Mi grupo</a>
                            <a class="dropdown-item" href="{{ url('/grupo-master/index') }}"><i class="bi bi-person-lines-fill" aria-hidden="true"></i> Alumnos</a>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navSeguimiento" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="bi bi-bar-chart-line" aria-hidden="true"></i> SEGUIMIENTO</a>
                        <div class="dropdown-menu" aria-labelledby="navSeguimiento">
                            <a class="dropdown-item" href="{{ url('/asistencia/index') }}"><i class="bi bi-calendar-check" aria-hidden="true"></i> Asistencia</a>
                            <a class="dropdown-item" href="{{ url('/pat/index') }}"><i class="bi bi-journal-text" aria-hidden="true"></i> PAT</a>
                            <a class="dropdown-item" href="{{ url('/diagnostico/index') }}"><i class="bi bi-clipboard2-data" aria-hidden="true"></i> Diagnóstico</a>
                            <a class="dropdown-item" href="{{ url('/evaluacion/index') }}"><i class="bi bi-person-check" aria-hidden="true"></i> Liberación</a>
                            <a class="dropdown-item" href="{{ url('/canalizacion/index') }}"><i class="bi bi-person-lines-fill" aria-hidden="true"></i> Contacto</a>
                        </div>
                    </li>
                    @if($gruposCount > 1)
                        <li class="nav-item"><a class="nav-link" href="{{ url('/site/clear-id-grupo') }}"><i class="bi bi-arrow-left-right" aria-hidden="true"></i> CAMBIAR GRUPO</a></li>
                    @endif
                @endif
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="{{ url('/site/index') }}" id="navMas" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="bi bi-three-dots" aria-hidden="true"></i> MÁS</a>
                    <div class="dropdown-menu" aria-labelledby="navMas">
                        <a class="dropdown-item" href="{{ url('/manual') }}"><i class="bi bi-book" aria-hidden="true"></i> MANUAL DE USUARIO</a>
                    </div>
                </li>
                <li>@include('partials.logout-button')</li>
            @else
                <li>@include('partials.logout-button')</li>
            @endif
        </ul>
    </div>
</nav>

