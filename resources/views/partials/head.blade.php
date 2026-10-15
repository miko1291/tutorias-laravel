<meta charset="utf-8">
<link rel="icon" href="{{ asset('images/tutorias.png') }}" type="image/x-icon">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
{{-- BS ICONS --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
{{-- FONT AWESOME - PARA CARGAR LOS ICONOS DEL GRIDVIEW --}}
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css">
@if(!empty($aos))
{{-- AOS --}}
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
@endif
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', config('app.name'))</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="{{ asset('css/site.css') }}?v={{ filemtime(public_path('css/site.css')) }}">
<link rel="stylesheet" href="{{ asset('css/pit-ui.css') }}?v={{ filemtime(public_path('css/pit-ui.css')) }}">
<link rel="stylesheet" href="{{ asset('css/app-shell.css') }}?v={{ filemtime(public_path('css/app-shell.css')) }}">
@stack('styles')
<link rel="stylesheet" href="{{ asset('css/tecm-theme.css') }}?v={{ filemtime(public_path('css/tecm-theme.css')) }}">
