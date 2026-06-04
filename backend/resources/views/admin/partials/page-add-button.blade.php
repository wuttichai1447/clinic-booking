@props(['href', 'label'])

<a
    href="{{ $href }}"
    {{ $attributes->merge(['class' => 'inline-flex justify-center items-center gap-2 bg-emerald-600 text-white px-4 py-2.5 rounded-lg font-medium text-sm sm:text-base hover:bg-emerald-700 transition']) }}
>
    <i data-lucide="plus" class="size-4 shrink-0" aria-hidden="true"></i>
    {{ $label }}
</a>
