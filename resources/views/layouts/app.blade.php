<!DOCTYPE html>
<html lang="es" class="h-100">
<head>
    @include('partials.head')
</head>
<body class="d-flex flex-column h-100 pit-app-shell">
<header class="sticky-top">
    @include('partials.navbar', ['ingresarUrl' => url('/site/login'), 'expedienteUrl' => '/expediente/ficha'])
</header>

<main id="main-content" role="main" class="flex-shrink-0 pit-main">
    <div class="container pit-content">
        @hasSection('breadcrumbs')
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
                    @yield('breadcrumbs')
                </ol>
            </nav>
        @endif
        @include('partials.flash')
        @yield('content')
    </div>
</main>

@include('partials.footer')

@include('partials.scripts')
@stack('scripts')
</body>
</html>

