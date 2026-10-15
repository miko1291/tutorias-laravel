@extends('layouts.app')

@section('title', 'Mi perfil de tutor')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Mi perfil</li>
@endsection

@section('content')
    <div class="tutor-profile">
        <div class="tutor-profile__hero">
            <div class="tutor-profile__avatar">{{ mb_strtoupper(mb_substr($tutor->nombre, 0, 1)) }}</div>
            <div>
                <span>Perfil del tutor</span>
                <h1>{{ $tutor->nombre_completo }}</h1>
                <p>Consulta tus datos y los grupos asignados.</p>
            </div>
        </div>
        <div class="tutor-profile__grid">
            <div class="card card-body">
                <h2>Datos de contacto</h2>
                <dl>
                    <dt>Correo</dt><dd>{{ $tutor->correo }}</dd>
                    <dt>Teléfono</dt><dd>{{ $tutor->telefono }}</dd>
                    <dt>Género</dt><dd>{{ $tutor->genero === 1 ? 'Femenino' : 'Masculino' }}</dd>
                </dl>
            </div>
            <div class="card card-body">
                <h2>Grupos asignados</h2>
                @forelse($tutor->grupoMasters as $grupo)
                    <a href="{{ url('/site/assign-id-grupo/'.$grupo->id) }}">{{ $grupo->nombre ?: 'Grupo '.$grupo->id }}</a>
                @empty
                    <p class="text-muted mb-0">Aún no tienes grupos asignados. El administrador puede configurarlos desde Grupos.</p>
                @endforelse
            </div>
        </div>
        <a class="btn btn-primary" href="{{ url('/site/index') }}">Volver al inicio</a>
    </div>
@endsection

@push('styles')
<style>
.tutor-profile{max-width:1000px;margin:15px auto 45px}.tutor-profile__hero{display:flex;align-items:center;gap:20px;padding:30px;border-radius:6px;background:linear-gradient(120deg,var(--tecm-blue),var(--tecm-blue));color:#fff}.tutor-profile__avatar{display:grid;place-items:center;width:70px;height:70px;border-radius:50%;background:#fff;color:var(--tecm-blue);font-size:1.9rem;font-weight:800}.tutor-profile__hero span{font-size:.72rem;letter-spacing:.1em;text-transform:uppercase;opacity:.8}.tutor-profile__hero h1{margin:6px 0;color:#fff;font-size:2rem}.tutor-profile__hero p{margin:0;color:rgba(255,255,255,.9)}.tutor-profile__grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin:18px 0}.tutor-profile h2{font-size:1.1rem;color:var(--tecm-blue);border-bottom:1px solid #e3eaf2;padding-bottom:10px}.tutor-profile dl{margin:0}.tutor-profile dt{margin-top:10px;color:#718092;font-size:.75rem;text-transform:uppercase}.tutor-profile dd{margin:2px 0;font-weight:600}.tutor-profile a:not(.btn){display:block;padding:8px 0}@media(max-width:700px){.tutor-profile__grid{grid-template-columns:1fr}}
</style>
@endpush
