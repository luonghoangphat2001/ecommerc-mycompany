@if ($paginator->hasPages())
    <nav class="pagination-wrap" role="navigation" aria-label="Pagination Navigation">
        <div class="pagination-summary tabular-nums">
            Hiển thị <strong>{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</strong> trong tổng số <strong>{{ number_format($paginator->total()) }}</strong> bản ghi
        </div>

        <div class="pagination-pages tabular-nums">
            @if ($paginator->onFirstPage())
                <span class="pagination-btn disabled" aria-disabled="true">&larr; Trước</span>
            @else
                <a class="pagination-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev">&larr; Trước</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="pagination-btn disabled">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="pagination-btn active" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="pagination-btn" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="pagination-btn" href="{{ $paginator->nextPageUrl() }}" rel="next">Sau &rarr;</a>
            @else
                <span class="pagination-btn disabled" aria-disabled="true">Sau &rarr;</span>
            @endif
        </div>
    </nav>
@endif
