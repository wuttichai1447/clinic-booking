<div class="inline-flex items-center gap-1.5 shrink-0 flex-wrap justify-end">
    <a
        href="{{ $editUrl }}"
        class="inline-flex items-center gap-1 rounded-lg px-2 py-1.5 text-xs font-medium text-amber-950 bg-amber-100 hover:bg-amber-200 border border-amber-300/80 transition"
        title="{{ $editLabel ?? 'แก้ไข' }}"
    >
        <i data-lucide="pencil" class="size-3.5 shrink-0" aria-hidden="true"></i>
        <span>{{ $editLabel ?? 'แก้ไข' }}</span>
    </a>
    @if (!empty($deleteUrl))
        <form method="POST" action="{{ $deleteUrl }}" class="inline" onsubmit="return confirm(@json($deleteConfirm ?? 'ลบรายการนี้?'))">
            @csrf
            @method('DELETE')
            <button
                type="submit"
                class="inline-flex items-center gap-1 rounded-lg px-2 py-1.5 text-xs font-medium text-white bg-red-600 hover:bg-red-700 border border-red-700 transition"
                title="{{ $deleteLabel ?? 'ลบ' }}"
            >
                <i data-lucide="trash-2" class="size-3.5 shrink-0" aria-hidden="true"></i>
                <span>{{ $deleteLabel ?? 'ลบ' }}</span>
            </button>
        </form>
    @endif
</div>
