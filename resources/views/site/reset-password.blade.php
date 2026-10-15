@extends('layouts.blank')
@section('title', 'Crear nueva contraseña')
@section('access-landing', true)
@section('hide-carousel', true)
@section('content')
<div class="auth-page auth-recovery">
    @include('site._brand')
    <section class="auth-panel"><span class="portal-kicker">Recupera tu acceso</span><h1>Crea tu nueva contraseña</h1><p>Usa al menos 8 caracteres. Elige una contraseña que no hayas utilizado en otros sitios.</p>
        @include('partials.flash')
        <form method="post" action="{{ route('password.update') }}">@csrf<input type="hidden" name="token" value="{{ $token }}">
            <div class="form-group"><label for="reset-email">Correo de tu cuenta</label><input type="email" id="reset-email" name="email" autocomplete="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $email) }}" required>@error('email')<div class="invalid-feedback" role="alert">{{ $message }}</div>@enderror @error('token')<div class="text-danger" role="alert">{{ $message }}</div>@enderror</div>
            <div class="form-group"><label for="passwordField">Nueva contraseña</label><div class="auth-password"><input type="password" id="passwordField" name="password" autocomplete="new-password" minlength="8" maxlength="72" class="form-control @error('password') is-invalid @enderror" required><button type="button" id="togglePassword" aria-label="Mostrar contraseña" aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button></div>@error('password')<div class="invalid-feedback d-block" role="alert">{{ $message }}</div>@enderror</div>
            <div class="form-group"><label for="password-confirmation">Confirma tu nueva contraseña</label><input type="password" id="password-confirmation" name="password_confirmation" autocomplete="new-password" minlength="8" maxlength="72" class="form-control" required></div>
            <button type="submit" class="portal-primary"><i class="bi bi-shield-check" aria-hidden="true"></i> Guardar nueva contraseña</button>
        </form>
        <div class="auth-help"><a href="{{ route('password.request') }}">Solicitar un nuevo enlace</a> · <a href="{{ route('login') }}">Volver al acceso</a></div>
    </section>
</div>
@endsection
@push('styles') @include('partials.portal-styles') @endpush
@push('scripts')<script src="{{ asset('js/login.js') }}?v={{ filemtime(public_path('js/login.js')) }}"></script>@endpush
