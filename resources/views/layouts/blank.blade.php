<!DOCTYPE html>
<html lang="es" class="h-100">

<head>
    @include('partials.head', ['aos' => true])
</head>

<body class="d-flex flex-column h-100 pit-app-shell pit-auth-shell">
@sectionMissing('access-landing')
<header class="sticky-top">
    @include('partials.navbar', ['ingresarUrl' => url('/site/acceso'), 'expedienteUrl' => '/expediente/index'])
</header>
@endif


<main role="main" class="flex-shrink-0 bg-white">
    @sectionMissing('hide-carousel')
    <section id="carouselExampleIndicators" class="carousel slide pit-public-hero" data-ride="carousel" aria-label="Actividades institucionales">
        <ol class="carousel-indicators">
            <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
            <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
            <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
            <li data-target="#carouselExampleIndicators" data-slide-to="3"></li>
            <li data-target="#carouselExampleIndicators" data-slide-to="4"></li>
            <li data-target="#carouselExampleIndicators" data-slide-to="5"></li>
        </ol>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="{{ asset('images/tuto1.png') }}" alt="Actividades institucionales del ITSVA" class="d-block pit-carousel-image">
                <div class="pit-carousel-caption"><span>Programa Institucional de Tutorías</span><strong>Tutorías ITSVA</strong><small>Expedientes, grupos y actividades del programa de tutorías.</small></div>
            </div>
            <div class="carousel-item">
                <img src="{{ asset('images/tuto2.png') }}" alt="Actividades institucionales del ITSVA" class="d-block pit-carousel-image">
                <div class="pit-carousel-caption"><span>Comunidad ITSVA</span><strong>Atención a estudiantes</strong><small>Consulta a tu tutor si necesitas orientación académica.</small></div>
            </div>
            <div class="carousel-item">
                <img src="{{ asset('images/tuto3.png') }}" alt="Actividades institucionales del ITSVA" class="d-block pit-carousel-image">
                <div class="pit-carousel-caption"><span>Seguimiento tutorial</span><strong>Información actualizada</strong><small>Consulta tus herramientas y mantén tu información actualizada.</small></div>
            </div>
            <div class="carousel-item">
                <img src="{{ asset('images/tuto1.png') }}" alt="Actividades institucionales del ITSVA" class="d-block pit-carousel-image">
                <div class="pit-carousel-caption"><span>Expedientes</span><strong>Datos personales y académicos</strong><small>Revisa tus respuestas antes de enviar el expediente.</small></div>
            </div>
            <div class="carousel-item">
                <img src="{{ asset('images/tuto2.png') }}" alt="Actividades de tutoría del ITSVA" class="d-block pit-carousel-image">
                <div class="pit-carousel-caption"><span>Actividades</span><strong>Seguimiento de tutoría</strong><small>Consulta las actividades y los avisos de tu grupo.</small></div>
            </div>
            <div class="carousel-item">
                <img src="{{ asset('images/tuto3.png') }}" alt="Comunidad estudiantil del ITSVA" class="d-block pit-carousel-image">
                <div class="pit-carousel-caption"><span>Ayuda</span><strong>Orientación académica</strong><small>Acude a tu tutor o a la coordinación cuando necesites apoyo.</small></div>
            </div>
        </div>
        <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Anterior</span>
        </a>
        <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Siguiente</span>
        </a>
    </section>
    @endif
    <div class="container">
        @yield('content')
    </div>
</main>

@include('partials.footer')

@include('partials.scripts')
@stack('scripts')
</body>

</html>

