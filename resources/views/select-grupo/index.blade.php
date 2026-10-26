@extends('layouts.app')

@php
    /** @var \App\Models\GrupoMaster[] $grupos */
@endphp

@section('title', 'Grupos del Tutor')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Grupos del Tutor</li>
@endsection

@section('content')
<div class="select-grupo-index ui-resource-page">
    @if (!$grupos->isEmpty())
        <x-ui.page-header eyebrow="Gestión tutorial" title="Selecciona un grupo" description="Elige el grupo para consultar sus alumnos, asistencias y expedientes." />
        <div class="grupo-selector-grid">
        @foreach ($grupos as $grupo)
            <a href="{{ url('/site/assign-id-grupo/' . $grupo->id) }}" class="grupo-selector-card">
                <span class="grupo-selector-icon"><i class="fas fa-users"></i></span><span class="grupo-selector-label">Grupo {{ $grupo->grupoLetra->letra_key }}</span>
                <strong>{{ $grupo->carrera->nombre }}</strong><small>{{ $grupo->semestre->nombre }}</small><span class="grupo-selector-action">Seleccionar <i class="fas fa-arrow-right"></i></span>
            </a>
        @endforeach
        </div>
    @else
        <x-ui.page-header eyebrow="Gestión tutorial" title="Mis grupos" description="Consulta los grupos asignados a tu cuenta." /><div class="card card-body ui-empty-state"><strong>No tienes grupos asignados</strong>Solicita a la coordinación que asigne tus grupos para comenzar.</div>
    @endif
</div>
@endsection

@push('styles')
<style>
.select-grupo-index{width:100%;max-width:1120px;margin:28px auto 60px;padding:0 18px}.grupo-selector-heading{padding:34px 38px;margin-bottom:26px;border-radius:6px;background:var(--tecm-blue);color:#fff;box-shadow:0 14px 30px rgba(28,58,94,.14)}.grupo-kicker{display:block;font-size:.7rem!important;letter-spacing:.14em;font-weight:800;opacity:.82}.grupo-selector-heading h1{margin:10px 0 8px;font-size:clamp(1.65rem,3vw,2.25rem)!important;font-weight:800;line-height:1.15}.grupo-selector-heading p{max-width:720px;margin:0;font-size:.98rem!important;line-height:1.6;opacity:.9}.grupo-selector-grid{display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr))!important;grid-auto-rows:1fr;gap:18px!important;align-items:stretch;width:100%}.grupo-selector-card{display:flex!important;flex-direction:column;width:100%!important;min-width:0;min-height:245px;padding:24px!important;border:1px solid #dbe6ef;border-radius:6px;background:#fff;color:#20344a;box-shadow:0 7px 20px rgba(29,62,94,.07);transition:transform .2s,box-shadow .2s,border-color .2s;overflow:hidden}.grupo-selector-card:hover{color:#1e6097;text-decoration:none;transform:translateY(-4px);border-color:#9fc4e1;box-shadow:0 14px 28px rgba(29,62,94,.13)}.grupo-selector-icon{display:grid;place-items:center;width:48px;height:48px;margin-bottom:22px;border-radius:6px;background:#eaf3fb;color:#246a9f;font-size:1.05rem}.grupo-selector-label{color:#6e8092;text-transform:uppercase;font-size:.72rem!important;letter-spacing:.08em;font-weight:800}.grupo-selector-card strong{display:block;margin-top:10px;font-size:1rem!important;line-height:1.45}.grupo-selector-card small{display:block;color:#718295;margin-top:8px;font-size:.88rem!important}.grupo-selector-action{margin-top:auto;padding-top:22px;color:#256da5;font-weight:700;font-size:.84rem!important}.grupo-selector-action i{margin-left:5px}@media(max-width:900px){.grupo-selector-grid{grid-template-columns:repeat(2,minmax(0,1fr))!important}}@media(max-width:600px){.select-grupo-index{margin-top:18px;padding:0 10px}.grupo-selector-grid{grid-template-columns:1fr!important}.grupo-selector-heading{padding:27px 24px}.grupo-selector-heading h1{font-size:1.65rem!important}.grupo-selector-card{min-height:210px;padding:21px!important}}
</style>
@endpush
