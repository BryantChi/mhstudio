@if ($paginator->hasPages())
    <nav class="pagination-wrapper" aria-label="分頁導覽">
        {{-- 手機版：僅上下頁 --}}
        <div class="d-flex justify-content-between d-sm-none">
            @if ($paginator->onFirstPage())
                <span class="btn btn-sm btn-outline-secondary disabled">
                    &laquo; 上一頁
                </span>
            @else
                <a class="btn btn-sm btn-outline-primary" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                    &laquo; 上一頁
                </a>
            @endif

            <span class="align-self-center text-muted small">
                第 {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }} 頁
            </span>

            @if ($paginator->hasMorePages())
                <a class="btn btn-sm btn-outline-primary" href="{{ $paginator->nextPageUrl() }}" rel="next">
                    下一頁 &raquo;
                </a>
            @else
                <span class="btn btn-sm btn-outline-secondary disabled">
                    下一頁 &raquo;
                </span>
            @endif
        </div>

        {{-- 桌面版：完整分頁 --}}
        <div class="d-none d-sm-flex align-items-center justify-content-between">
            <div class="text-muted small">
                顯示第 <span class="fw-semibold">{{ $paginator->firstItem() }}</span>
                至 <span class="fw-semibold">{{ $paginator->lastItem() }}</span> 筆，
                共 <span class="fw-semibold">{{ $paginator->total() }}</span> 筆
            </div>

            <ul class="pagination pagination-sm mb-0">
                {{-- Previous --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link" aria-hidden="true">&lsaquo;</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="上一頁">&lsaquo;</a>
                    </li>
                @endif

                {{-- Pages --}}
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <li class="page-item disabled"><span class="page-link">{{ $element }}</span></li>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                            @else
                                <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="下一頁">&rsaquo;</a>
                    </li>
                @else
                    <li class="page-item disabled">
                        <span class="page-link" aria-hidden="true">&rsaquo;</span>
                    </li>
                @endif
            </ul>
        </div>
    </nav>
@endif
