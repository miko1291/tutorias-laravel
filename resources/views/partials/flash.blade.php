@php
    $alertTypes = [
        'error' => 'danger',
        'danger' => 'danger',
        'success' => 'success',
        'info' => 'info',
        'warning' => 'warning',
    ];
    $alertIcons = [
        'danger' => 'exclamation-triangle',
        'success' => 'check-circle',
        'info' => 'info-circle',
        'warning' => 'exclamation-circle',
    ];
@endphp
@foreach($alertTypes as $type => $class)
    @if(session($type))
        <div class="alert ui-alert alert-{{ $class }} alert-dismissible fade show" role="alert">
            <i class="fas fa-{{ $alertIcons[$class] }}" aria-hidden="true"></i>
            {{ session($type) }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
@endforeach
