@if ($paginator->lastPage() > 1)
<div class="h2-pagination">
    @if ($paginator->onFirstPage())
        <span>‹ Prev</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}">‹ Prev</a>
    @endif

    @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
        <a href="{{ $url }}" class="{{ $page === $paginator->currentPage() ? 'on' : '' }}">{{ $page }}</a>
    @endforeach

    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}">Next ›</a>
    @else
        <span>Next ›</span>
    @endif
</div>
@endif
