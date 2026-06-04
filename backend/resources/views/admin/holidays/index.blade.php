@extends('admin.layout')
@section('title', 'วันหยุดคลินิก')
@section('content')
<div class="flex flex-col xs:flex-row xs:justify-between xs:items-center gap-3 mb-5 sm:mb-6">
    <div>
        <h1 class="text-xl sm:text-2xl font-semibold">วันหยุดคลินิก</h1>
        <p class="text-sm text-slate-600 mt-1">วันที่ตรงกับรายการนี้จะไม่แสดงช่วงเวลาให้จอง (ทุกสาขา หรือเฉพาะคลินิก)</p>
    </div>
    @include('admin.partials.page-add-button', [
        'href' => route('admin.holidays.create'),
        'label' => 'เพิ่มวันหยุด',
    ])
</div>

<form method="GET" class="mb-4 flex flex-wrap gap-2 items-end">
    <div>
        <label class="text-sm font-medium block mb-1">กรองตามคลินิก</label>
        <select name="clinic_id" class="border rounded-lg px-3 py-2 text-sm min-w-[200px]" onchange="this.form.submit()">
            <option value="">ทั้งหมด</option>
            @foreach ($clinics as $c)
                <option value="{{ $c->id }}" @selected($filterClinicId === $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
    </div>
</form>

<div class="bg-white rounded-xl border overflow-x-auto shadow-sm">
    <table class="w-full text-sm min-w-[520px]">
        <thead class="bg-slate-50 text-left">
            <tr>
                <th class="px-4 py-3">วันที่</th>
                <th class="px-4 py-3">คลินิก</th>
                <th class="px-4 py-3">ชื่อวันหยุด</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($holidays as $h)
                <tr class="border-t">
                    <td class="px-4 py-3 font-medium">{{ $h->date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3">{{ $h->clinic?->name ?? 'ทุกสาขา' }}</td>
                    <td class="px-4 py-3">{{ $h->name ?: '—' }}</td>
                    <td class="px-4 py-3 text-right">
                        @include('admin.partials.table-actions', [
                            'editUrl' => route('admin.holidays.edit', $h),
                            'deleteUrl' => route('admin.holidays.destroy', $h),
                            'editLabel' => 'แก้ไข',
                            'deleteLabel' => 'ลบ',
                            'deleteConfirm' => 'ลบวันหยุดนี้?',
                        ])
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">ยังไม่มีวันหยุด</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@include('admin.partials.pagination', ['paginator' => $holidays])
@endsection
