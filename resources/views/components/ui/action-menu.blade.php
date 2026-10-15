@props(['label' => 'Acciones'])

<div class="dropdown ui-action-menu">
    <button class="btn btn-sm btn-light ui-action-menu__button" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fas fa-ellipsis-h" aria-hidden="true"></i><span class="sr-only">{{ $label }}</span>
    </button>
    <div class="dropdown-menu dropdown-menu-right">
        {{ $slot }}
    </div>
</div>
