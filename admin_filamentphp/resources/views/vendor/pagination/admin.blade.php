@if ($paginator->hasPages())
    <nav class="pagination-wrap" role="navigation" aria-label="Pagination Navigation">
        <div class="pagination-summary">
            Hiển thị {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} / {{ $paginator->total() }}
        </div>

        <div class="pagination-pages">
            @if ($paginator->onFirstPage())
                <span class="pagination-btn disabled">Trước</span>
            @else
                <a class="pagination-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev">Trước</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="pagination-btn disabled">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="pagination-btn active">{{ $page }}</span>
                        @else
                            <a class="pagination-btn" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="pagination-btn" href="{{ $paginator->nextPageUrl() }}" rel="next">Sau</a>
            @else
                <span class="pagination-btn disabled">Sau</span>
            @endif
        </div>
    </nav>
@endif
