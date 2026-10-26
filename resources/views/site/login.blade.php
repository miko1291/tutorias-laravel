@extends('layouts.blank')
@section('title', 'Iniciar sesión')
@section('access-landing', true)
@section('hide-carousel', true)
@section('content')
<div class="auth-page auth-login">
    @include('site._brand')
    <div class="auth-layout">
        <section class="auth-story"><span class="portal-kicker">{{ $perfil['nombre'] }} · Programa de Tutorías</span><h1>Iniciar sesión</h1><p>{{ $perfil['descripcion'] }}</p><div class="auth-photo"><img src="{{ asset('images/tuto1.png') }}" alt="Actividades de la comunidad ITSVA"><span>Instituto Tecnológico Superior de Valladolid</span></div></section>
        <section class="auth-panel"><a class="auth-back" href="{{ url('/site/acceso') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Cambiar perfil de acceso</a><h2>{{ $perfil['titulo'] }}</h2><p>Ingresa con el usuario y la contraseña de tu cuenta.</p>
        @include('partials.flash')
        <form method="post" action="{{ url('/site/login') }}" id="login-form">
            @csrf
            <div class="form-group"><label for="login-username">Usuario</label><input class="form-control @error('username') is-invalid @enderror" id="login-username" name="username" autocomplete="username" value="{{ old('username') }}" placeholder="Escribe tu usuario" required autofocus>@error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="form-group"><label for="passwordField">Contraseña</label><div class="auth-password"><input type="password" class="form-control @error('password') is-invalid @enderror" id="passwordField" name="password" autocomplete="current-password" placeholder="Escribe tu contraseña" required><button type="button" id="togglePassword" aria-label="Mostrar contraseña" aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button></div>@error('password')<div class="invalid-feedback d-block" role="alert">{{ $message }}</div>@enderror</div>
            <input type="hidden" name="rememberMe" value="0"><label class="auth-remember" for="rememberMe"><input id="rememberMe" type="checkbox" name="rememberMe" value="1" @checked(old('rememberMe', false))><span>Mantener mi sesión en este dispositivo</span></label>
            <button type="submit" class="portal-primary" name="login-button">Ingresar al sistema <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
        </form>
        <div class="auth-help"><strong>¿No puedes ingresar?</strong><br><a href="{{ route('password.request') }}"><i class="bi bi-key" aria-hidden="true"></i> Olvidé mi contraseña</a><br>Recibe un enlace en el correo registrado en tu cuenta.<br><a href="{{ url('/manual?perfil='.$perfilClave) }}">Ver guía de acceso</a></div></section>
    </div>
</div>
@endsection
@push('styles') @include('partials.portal-styles') @endpush
@push('scripts')<script src="{{ asset('js/login.js') }}?v={{ filemtime(public_path('js/login.js')) }}"></script>@endpush
