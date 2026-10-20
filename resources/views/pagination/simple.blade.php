@if ($paginator->hasPages())
    <nav class="pagination" style="border:none;padding:0;background:none;">
        @if ($paginator->onFirstPage())
            <span>&laquo; Sebelumnya</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}">&laquo; Sebelumnya</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span>{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="current">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}">Berikutnya &raquo;</a>
        @else
            <span>Berikutnya &raquo;</span>
        @endif
    </nav>
@endif
