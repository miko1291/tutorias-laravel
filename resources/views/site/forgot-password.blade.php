@extends('layouts.blank')
@section('title', 'Recuperar mi acceso')
@section('access-landing', true)
@section('hide-carousel', true)
@section('content')
<div class="auth-page auth-recovery">
    @include('site._brand')
    <section class="auth-panel"><a class="auth-back" href="{{ route('login') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Volver al acceso</a><span class="portal-kicker">Recuperación de contraseña</span><h1>¿Olvidaste tu contraseña?</h1><p>Escribe el correo registrado en tu cuenta. Te enviaremos un enlace para crear una nueva contraseña.</p>
        @include('partials.flash')
        <form method="post" action="{{ route('password.email') }}">@csrf
            <div class="form-group"><label for="recovery-email">Correo de tu cuenta</label><input type="email" name="email" id="recovery-email" class="form-control @error('email') is-invalid @enderror" autocomplete="email" value="{{ old('email') }}" placeholder="tu-correo@ejemplo.com" required autofocus>@error('email')<div class="invalid-feedback" role="alert">{{ $message }}</div>@enderror</div>
            <button type="submit" class="portal-primary"><i class="bi bi-envelope" aria-hidden="true"></i> Enviar enlace de recuperación</button>
        </form>
        <div class="auth-help"><strong>¿No encuentras el mensaje?</strong><br>Revisa spam y confirma que escribiste el correo de tu cuenta. El enlace caduca en {{ config('auth.passwords.users.expire') }} minutos. Si no tienes acceso a ese correo, solicita ayuda a la coordinación.</div>
    </section>
</div>
@endsection
@push('styles') @include('partials.portal-styles') @endpush
