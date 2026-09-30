@if ($paginator->hasPages())
    <nav class="flex items-center justify-between" role="navigation" aria-label="Pagination">
        <div class="flex flex-1 items-center gap-1">
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" class="cursor-not-allowed rounded-md px-3 py-1.5 text-sm text-slate-300">&laquo; Prev</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="rounded-md px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-100">&laquo; Prev</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-3 py-1.5 text-sm text-slate-400">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="rounded-md bg-brand px-3 py-1.5 text-sm font-medium text-white">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="rounded-md px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-100">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="rounded-md px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-100">Next &raquo;</a>
            @else
                <span aria-disabled="true" class="cursor-not-allowed rounded-md px-3 py-1.5 text-sm text-slate-300">Next &raquo;</span>
            @endif
        </div>
    </nav>
@endif
