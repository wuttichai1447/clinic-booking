@extends('admin.layout')
@section('title', 'โปรโมชั่น')
@section('content')
<div class="flex flex-col xs:flex-row xs:justify-between xs:items-center gap-3 mb-5 sm:mb-6">
    <h1 class="text-xl sm:text-2xl font-semibold">โปรโมชั่น</h1>
    <a href="{{ route('admin.promotions.create') }}" class="inline-flex justify-center bg-emerald-600 text-white px-4 py-2.5 rounded-lg font-medium text-sm sm:text-base">+ เพิ่ม</a>
</div>
<div class="bg-white rounded-xl border overflow-x-auto shadow-sm">
    <table class="w-full text-sm min-w-[640px]">
        <thead class="bg-slate-50 text-left">
            <tr><th class="px-4 py-3">รหัส</th><th class="px-4 py-3">ชื่อ</th><th class="px-4 py-3">ส่วนลด</th><th class="px-4 py-3">ใช้แล้ว</th><th class="px-4 py-3">สถานะ</th><th></th></tr>
        </thead>
        <tbody>
            @forelse ($promotions as $p)
                <tr class="border-t">
                    <td class="px-4 py-3 font-mono font-bold">{{ $p->code }}</td>
                    <td class="px-4 py-3">{{ $p->title }}</td>
                    <td class="px-4 py-3">{{ $p->type === 'percent' ? $p->value.'%' : '฿'.number_format($p->value) }}</td>
                    <td class="px-4 py-3">{{ $p->used_count }}{{ $p->max_uses ? '/'.$p->max_uses : '' }}</td>
                    <td class="px-4 py-3">{{ $p->is_active ? 'เปิด' : 'ปิด' }}</td>
                    <td class="px-4 py-3 text-right">
                        @include('admin.partials.table-actions', [
                            'editUrl' => route('admin.promotions.edit', $p),
                            'deleteUrl' => route('admin.promotions.destroy', $p),
                        ])
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-slate-500">ไม่พบโปรโมชั่น</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@include('admin.partials.pagination', ['paginator' => $promotions])
@endsection
