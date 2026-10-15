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
                <div class="pit-carousel-caption"><span>Programa Institucional de Tutorías</span><strong>Acompañamiento integral para estudiantes</strong><small>Fortalecemos tu trayectoria académica y bienestar.</small></div>
            </div>
            <div class="carousel-item">
                <img src="{{ asset('images/tuto2.png') }}" alt="Actividades institucionales del ITSVA" class="d-block pit-carousel-image">
                <div class="pit-carousel-caption"><span>Comunidad ITSVA</span><strong>Construimos oportunidades juntos</strong><small>La tutoría conecta, orienta y acompaña.</small></div>
            </div>
            <div class="carousel-item">
                <img src="{{ asset('images/tuto3.png') }}" alt="Actividades institucionales del ITSVA" class="d-block pit-carousel-image">
                <div class="pit-carousel-caption"><span>Seguimiento tutorial</span><strong>Tu desarrollo es nuestra prioridad</strong><small>Consulta tus herramientas y mantén tu información actualizada.</small></div>
            </div>
            <div class="carousel-item">
                <img src="{{ asset('images/tuto1.png') }}" alt="Visión del Programa Institucional de Tutorías" class="d-block pit-carousel-image">
                <div class="pit-carousel-caption"><span>Visión</span><strong>Una tutoría que transforma</strong><small>Impactamos positivamente en la calidad educativa y el desarrollo integral del estudiante.</small></div>
            </div>
            <div class="carousel-item">
                <img src="{{ asset('images/tuto2.png') }}" alt="Misión del Programa Institucional de Tutorías" class="d-block pit-carousel-image">
                <div class="pit-carousel-caption"><span>Misión</span><strong>Acompañar para avanzar</strong><small>Contribuimos a la calidad educativa y a la permanencia mediante estrategias de acompañamiento tutorial.</small></div>
            </div>
            <div class="carousel-item">
                <img src="{{ asset('images/tuto3.png') }}" alt="Objetivo del Programa Institucional de Tutorías" class="d-block pit-carousel-image">
                <div class="pit-carousel-caption"><span>Objetivo</span><strong>Fortalecer cada trayectoria</strong><small>Favorecemos el rendimiento académico, la eficiencia terminal y la formación integral.</small></div>
            </div>
        </div>
        <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
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

