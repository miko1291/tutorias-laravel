@props(['title' => 'Registros', 'paginator' => null])

<section class="card ui-table-card" aria-label="{{ $title }}">
    <div class="ui-panel-heading">
        <h2>{{ $title }}</h2>
        @if ($paginator)
            <span class="ui-count">{{ number_format($paginator->total()) }} registros</span>
        @endif
    </div>
    <div class="table-responsive">{{ $slot }}</div>
    @if ($paginator)
        <div class="ui-panel-footer">
            <p>Mostrando <strong>{{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }}</strong> de <strong>{{ number_format($paginator->total()) }}</strong> registros</p>
            @if ($paginator->hasPages())
                {{ $paginator->withQueryString()->links() }}
            @endif
        </div>
    @endif
</section>
