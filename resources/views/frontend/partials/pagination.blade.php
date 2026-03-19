{{-- 前台專用分頁 — 不依賴 Bootstrap，純 mh-studio.scss --}}
@if ($paginator->hasPages())
<nav class="fe-pagination" aria-label="分頁導覽">
    {{-- 手機版 --}}
    <div class="fe-pagination-mobile">
        @if ($paginator->onFirstPage())
            <span class="fe-page-btn disabled">&laquo; 上一頁</span>
        @else
            <a class="fe-page-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo; 上一頁</a>
        @endif

        <span class="fe-page-info">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>

        @if ($paginator->hasMorePages())
            <a class="fe-page-btn" href="{{ $paginator->nextPageUrl() }}" rel="next">下一頁 &raquo;</a>
        @else
            <span class="fe-page-btn disabled">下一頁 &raquo;</span>
        @endif
    </div>

    {{-- 桌面版 --}}
    <div class="fe-pagination-desktop">
        <span class="fe-page-summary">
            顯示第 {{ $paginator->firstItem() }} 至 {{ $paginator->lastItem() }} 筆，共 {{ $paginator->total() }} 筆
        </span>

        <ul class="fe-page-list">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <li class="fe-page-item disabled"><span>&lsaquo;</span></li>
            @else
                <li class="fe-page-item"><a href="{{ $paginator->previousPageUrl() }}" rel="prev">&lsaquo;</a></li>
            @endif

            {{-- Pages --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="fe-page-item disabled"><span>{{ $element }}</span></li>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="fe-page-item active"><span>{{ $page }}</span></li>
                        @else
                            <li class="fe-page-item"><a href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <li class="fe-page-item"><a href="{{ $paginator->nextPageUrl() }}" rel="next">&rsaquo;</a></li>
            @else
                <li class="fe-page-item disabled"><span>&rsaquo;</span></li>
            @endif
        </ul>
    </div>
</nav>
@endif
