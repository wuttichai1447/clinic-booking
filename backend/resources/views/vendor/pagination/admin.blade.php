@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="inline-flex flex-wrap items-center justify-center gap-1">
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center px-3 py-2 text-sm font-medium text-slate-400 bg-white border border-slate-200 rounded-lg cursor-default">
                ก่อนหน้า
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-3 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 hover:text-slate-900 transition">
                ก่อนหน้า
            </a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="inline-flex items-center px-3 py-2 text-sm font-medium text-slate-500 bg-white border border-slate-200 rounded-lg cursor-default">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page" class="inline-flex items-center min-w-[2.5rem] justify-center px-3 py-2 text-sm font-semibold text-white bg-emerald-600 border border-emerald-600 rounded-lg">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}" class="inline-flex items-center min-w-[2.5rem] justify-center px-3 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition" aria-label="ไปหน้า {{ $page }}">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-3 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 hover:text-slate-900 transition">
                ถัดไป
            </a>
        @else
            <span class="inline-flex items-center px-3 py-2 text-sm font-medium text-slate-400 bg-white border border-slate-200 rounded-lg cursor-default">
                ถัดไป
            </span>
        @endif
    </nav>
@endif
