@php
    /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator */
@endphp
@if ($paginator->total() > 0)
    <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white rounded-xl border px-4 py-3 shadow-sm">
        <p class="text-sm text-slate-600 text-center sm:text-left">
            แสดง
            <span class="font-medium text-slate-900">{{ $paginator->firstItem() }}</span>–<span class="font-medium text-slate-900">{{ $paginator->lastItem() }}</span>
            จาก <span class="font-medium text-slate-900">{{ number_format($paginator->total()) }}</span> รายการ
            @if ($paginator->hasPages())
                · หน้า {{ $paginator->currentPage() }}/{{ $paginator->lastPage() }}
            @endif
        </p>
        {{ $paginator->links('vendor.pagination.admin') }}
    </div>
@endif
