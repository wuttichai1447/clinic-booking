@props([
    'cancelUrl',
    'submitLabel' => 'บันทึก',
    'cancelLabel' => 'ยกเลิกและกลับ',
])

<div class="flex flex-col sm:flex-row sm:items-center gap-3 pt-2">
    <button
        type="submit"
        class="inline-flex justify-center items-center gap-2 w-full sm:w-auto bg-amber-400 text-amber-950 px-6 py-2.5 rounded-lg font-medium hover:bg-amber-500 border border-amber-500/80 transition shadow-sm"
    >
        <i data-lucide="save" class="size-4 shrink-0" aria-hidden="true"></i>
        {{ $submitLabel }}
    </button>
    <a
        href="{{ $cancelUrl }}"
        class="inline-flex justify-center items-center gap-2 w-full sm:w-auto px-6 py-2.5 rounded-lg bg-red-600 text-white font-medium text-sm hover:bg-red-700 border border-red-700 transition shadow-sm"
    >
        <i data-lucide="x" class="size-4 shrink-0" aria-hidden="true"></i>
        {{ $cancelLabel }}
    </a>
</div>
