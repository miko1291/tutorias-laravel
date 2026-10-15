@props([
    'status' => 'neutral',
    'label',
])

@php
    $classes = [
        'active' => 'ui-status-badge--success',
        'success' => 'ui-status-badge--success',
        'warning' => 'ui-status-badge--warning',
        'inactive' => 'ui-status-badge--muted',
        'danger' => 'ui-status-badge--danger',
        'neutral' => 'ui-status-badge--muted',
    ];
@endphp

<span class="ui-status-badge {{ $classes[$status] ?? $classes['neutral'] }}">
    <span class="ui-status-badge__dot" aria-hidden="true"></span>{{ $label }}
</span>
