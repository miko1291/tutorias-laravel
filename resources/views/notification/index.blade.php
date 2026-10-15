@extends('layouts.app')

@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\Notification> $notifications */
@endphp

@section('title', 'Notificaciones')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Notificaciones</li>
@endsection

@section('content')
<div class="notification-page">
    <div class="notification-heading"><span>CONTROL DE TUTORÍAS</span><h1>Notificaciones</h1><p>Avisos importantes sobre tu seguimiento institucional.</p><small class="notification-heading__hint"><i class="fas fa-info-circle"></i> Selecciona un aviso para marcarlo como leído y abrir su detalle.</small></div>
    @if ($notifications->isEmpty())
        <div class="notification-empty"><i class="fas fa-check-circle"></i><strong>No tienes notificaciones</strong><span>Cuando exista un aviso, aparecerá aquí.</span></div>
    @else
        <div class="notification-list">
            @foreach ($notifications as $notification)
                <a class="notification-item {{ $notification->read_at === null ? 'is-unread' : '' }}" href="{{ url('/notification/read/' . $notification->id) }}">
                    <i class="fas fa-{{ $notification->type === 'warning' ? 'exclamation-circle' : 'bell' }}"></i>
                    <span><strong>{{ $notification->title }}</strong><small>{{ $notification->message }}</small><em>{{ \Illuminate\Support\Carbon::createFromTimestamp((int) $notification->created_at)->format('d/m/Y H:i') }}</em></span>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
.notification-page{max-width:850px;margin:25px auto 55px}.notification-heading{padding:28px 30px;background:var(--tecm-blue);color:#fff;border-radius:8px}.notification-heading span{font-size:.7rem;letter-spacing:.12em;opacity:.8}.notification-heading h1{margin:8px 0 4px}.notification-heading p{margin:0;opacity:.82}.notification-list{margin-top:18px;border:1px solid #dce4eb;border-radius:8px;background:#fff}.notification-item{display:flex;gap:15px;padding:19px 21px;border-bottom:1px solid #e7edf2;color:#263d53}.notification-item:last-child{border-bottom:0}.notification-item:hover{text-decoration:none;background:#f8fafc}.notification-item>i{color:#7f91a2;margin-top:3px}.notification-item.is-unread{border-left:4px solid var(--tecm-blue);background:#f5f9fc}.notification-item span{display:flex;flex-direction:column;gap:4px}.notification-item small{color:#65788b;line-height:1.5}.notification-item em{font-size:.75rem;color:#91a0ad;font-style:normal}.notification-empty{display:flex;flex-direction:column;align-items:center;gap:7px;padding:55px 20px;margin-top:20px;border:1px solid #dce4eb;background:#fff;color:#607589;border-radius:8px}.notification-empty i{font-size:2rem;color:var(--tecm-blue)}
.notification-heading__hint{display:block;margin-top:15px;color:rgba(255,255,255,.75)}
</style>
@endpush
