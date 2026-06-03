<div class="inline-flex items-center gap-1.5 shrink-0">
    <a
        href="{{ $editUrl }}"
        class="inline-flex size-9 items-center justify-center rounded-lg text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200/70 transition"
        title="{{ $editLabel ?? 'แก้ไข' }}"
        aria-label="{{ $editLabel ?? 'แก้ไข' }}"
    >
        <i data-lucide="pencil" class="size-4"></i>
    </a>
    @if (!empty($deleteUrl))
        <form method="POST" action="{{ $deleteUrl }}" class="inline" onsubmit="return confirm(@json($deleteConfirm ?? 'ลบรายการนี้?'))">
            @csrf
            @method('DELETE')
            <button
                type="submit"
                class="inline-flex size-9 items-center justify-center rounded-lg text-red-700 bg-red-50 hover:bg-red-100 border border-red-200/70 transition"
                title="{{ $deleteLabel ?? 'ลบ' }}"
                aria-label="{{ $deleteLabel ?? 'ลบ' }}"
            >
                <i data-lucide="trash-2" class="size-4"></i>
            </button>
        </form>
    @endif
</div>
