@extends('admin.layout')
@section('title', 'คลินิก')
@section('content')
<div class="flex flex-col xs:flex-row xs:justify-between xs:items-center gap-3 mb-5 sm:mb-6">
    <h1 class="text-xl sm:text-2xl font-semibold">คลินิก</h1>
    @include('admin.partials.page-add-button', [
        'href' => route('admin.clinics.create'),
        'label' => 'เพิ่มคลินิก',
    ])
</div>
<div class="bg-white rounded-xl border overflow-x-auto shadow-sm">
    <table class="w-full text-sm min-w-[560px]">
        <thead class="bg-slate-50 text-left">
            <tr>
                <th class="px-4 py-3">ID</th>
                <th class="px-4 py-3">ชื่อ</th>
                <th class="px-4 py-3">โทร</th>
                <th class="px-4 py-3">สถานะ</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($clinics as $c)
                <tr class="border-t">
                    <td class="px-4 py-3 font-mono text-xs">{{ $c->id }}</td>
                    <td class="px-4 py-3">{{ $c->name }}</td>
                    <td class="px-4 py-3">{{ $c->phone }}</td>
                    <td class="px-4 py-3">{{ $c->is_active ? 'เปิด' : 'ปิด' }}</td>
                    <td class="px-4 py-3 text-right">
                        @include('admin.partials.table-actions', [
                            'editUrl' => route('admin.clinics.edit', $c),
                            'deleteUrl' => route('admin.clinics.destroy', $c),
                            'editLabel' => 'แก้ไข',
                            'deleteLabel' => 'ลบ',
                            'deleteConfirm' => 'ลบคลินิก '.$c->name.'?',
                        ])
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">ไม่พบคลินิก</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@include('admin.partials.pagination', ['paginator' => $clinics])
@endsection
