@extends('admin.layout')
@section('title', 'ลานักบำบัด')
@section('content')
<div class="flex flex-col xs:flex-row xs:justify-between xs:items-center gap-3 mb-5 sm:mb-6">
    <div>
        <h1 class="text-xl sm:text-2xl font-semibold">ลานักบำบัด</h1>
        <p class="text-sm text-slate-600 mt-1">บันทึกวันลาของนักบำบัด — ช่วงวันที่ลาจะไม่แสดงเวลาว่างให้ลูกค้าจอง</p>
    </div>
    @include('admin.partials.page-add-button', [
        'href' => route('admin.staff-leaves.create'),
        'label' => 'บันทึกการลา',
    ])
</div>

<form method="GET" class="mb-4 flex flex-wrap gap-3 items-end">
    <div>
        <label class="text-sm font-medium block mb-1">คลินิก</label>
        <select name="clinic_id" class="border rounded-lg px-3 py-2 text-sm min-w-[180px]" onchange="this.form.submit()">
            <option value="">ทุกคลินิก</option>
            @foreach ($clinics as $c)
                <option value="{{ $c->id }}" @selected($filterClinicId === $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-sm font-medium block mb-1">นักบำบัด</label>
        <select name="therapist_id" class="border rounded-lg px-3 py-2 text-sm min-w-[220px]" onchange="this.form.submit()">
            <option value="">ทุกคน</option>
            @foreach ($therapists as $t)
                <option value="{{ $t->id }}" @selected($filterTherapistId === $t->id)>
                    {{ $t->name }} ({{ $t->clinic?->name ?? '—' }})
                </option>
            @endforeach
        </select>
    </div>
</form>

<div class="bg-white rounded-xl border overflow-x-auto shadow-sm">
    <table class="w-full text-sm min-w-[640px]">
        <thead class="bg-slate-50 text-left">
            <tr>
                <th class="px-4 py-3">นักบำบัด</th>
                <th class="px-4 py-3">คลินิก</th>
                <th class="px-4 py-3">ช่วงวันที่</th>
                <th class="px-4 py-3">ประเภท</th>
                <th class="px-4 py-3">หมายเหตุ</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($leaves as $row)
                <tr class="border-t">
                    <td class="px-4 py-3 font-medium">{{ $row->therapist?->name }}</td>
                    <td class="px-4 py-3">{{ $row->therapist?->clinic?->name ?? '—' }}</td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        {{ $row->start_date->format('d/m/Y') }}
                        @if ($row->end_date->format('Y-m-d') !== $row->start_date->format('Y-m-d'))
                            – {{ $row->end_date->format('d/m/Y') }}
                        @endif
                    </td>
                    <td class="px-4 py-3">{{ $leaveTypes[$row->leave_type] ?? $row->leave_type }}</td>
                    <td class="px-4 py-3 text-slate-600 max-w-[200px] truncate">{{ $row->note ?: '—' }}</td>
                    <td class="px-4 py-3 text-right">
                        @include('admin.partials.table-actions', [
                            'editUrl' => route('admin.staff-leaves.edit', $row),
                            'deleteUrl' => route('admin.staff-leaves.destroy', $row),
                            'editLabel' => 'แก้ไข',
                            'deleteLabel' => 'ลบ',
                            'deleteConfirm' => 'ลบรายการลานี้?',
                        ])
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">ยังไม่มีรายการลา</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@include('admin.partials.pagination', ['paginator' => $leaves])
@endsection
