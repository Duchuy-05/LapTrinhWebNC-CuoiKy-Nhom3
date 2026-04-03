@if ($paginator->hasPages())
    <div class="pager">
        @if ($paginator->onFirstPage())
            <span class="button button-ghost button-small is-disabled">Trang trước</span>
        @else
            <a class="button button-ghost button-small" href="{{ $paginator->previousPageUrl() }}">Trang trước</a>
        @endif

        <span class="pager__meta">Trang {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>

        @if ($paginator->hasMorePages())
            <a class="button button-muted button-small" href="{{ $paginator->nextPageUrl() }}">Trang tiếp</a>
        @else
            <span class="button button-muted button-small is-disabled">Trang tiếp</span>
        @endif
    </div>
@endif