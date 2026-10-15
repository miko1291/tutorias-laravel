@props([
    'eyebrow' => null,
    'title',
    'description' => null,
])

<div class="ui-page-header">
    <div>
        @if($eyebrow)
            <span class="ui-page-header__eyebrow">{{ $eyebrow }}</span>
        @endif
        <h1 class="ui-page-header__title">{{ $title }}</h1>
        @if($description)
            <p class="ui-page-header__description">{{ $description }}</p>
        @endif
    </div>
    @if(trim((string) $slot) !== '')
        <div class="ui-page-header__actions">{{ $slot }}</div>
    @endif
</div>
