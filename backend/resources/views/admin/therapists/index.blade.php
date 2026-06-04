@extends('admin.layout')
@section('title', 'นักบำบัด')
@section('content')
<div class="flex flex-col xs:flex-row xs:justify-between xs:items-center gap-3 mb-5 sm:mb-6">
    <h1 class="text-xl sm:text-2xl font-semibold">นักบำบัด</h1>
    @include('admin.partials.page-add-button', [
        'href' => route('admin.therapists.create'),
        'label' => 'เพิ่มนักบำบัด',
    ])
</div>
<form method="GET" class="mb-4">
    <select name="clinic_id" class="w-full sm:w-auto max-w-full rounded-lg border px-3 py-2 text-sm" onchange="this.form.submit()">
        <option value="">ทุกคลินิก</option>
        @foreach ($clinics as $c)
            <option value="{{ $c->id }}" @selected($clinicId == $c->id)>{{ $c->name }}</option>
        @endforeach
    </select>
</form>
<div class="bg-white rounded-xl border overflow-x-auto shadow-sm">
    <table class="w-full text-sm min-w-[360px] xs:min-w-[520px]">
        <thead class="bg-slate-50 text-left">
            <tr>
                <th class="px-2 py-2.5 sm:px-4 sm:py-3 min-w-[7rem]">ชื่อ</th>
                <th class="px-2 py-2.5 sm:px-4 sm:py-3 min-w-[6rem]">คลินิก</th>
                <th class="px-2 py-2.5 sm:px-4 sm:py-3 min-w-[5rem] hidden xs:table-cell">ความเชี่ยวชาญ</th>
                <th class="px-2 py-2.5 sm:px-4 sm:py-3 min-w-[9rem] text-right sticky right-0 bg-slate-50 z-10 shadow-[-6px_0_8px_-6px_rgba(15,23,42,0.12)]">การดำเนินการ</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($therapists as $t)
                <tr class="border-t">
                    <td class="px-2 py-2.5 sm:px-4 sm:py-3">{{ $t->name }}</td>
                    <td class="px-2 py-2.5 sm:px-4 sm:py-3">{{ $t->clinic?->name }}</td>
                    <td class="px-2 py-2.5 sm:px-4 sm:py-3 hidden xs:table-cell">{{ $t->specialty }}</td>
                    <td class="px-2 py-2.5 sm:px-4 sm:py-3 text-right whitespace-nowrap sticky right-0 bg-white z-10 shadow-[-6px_0_8px_-6px_rgba(15,23,42,0.08)]">
                        @include('admin.partials.table-actions', [
                            'editUrl' => route('admin.therapists.edit', $t),
                            'deleteUrl' => route('admin.therapists.destroy', $t),
                            'editLabel' => 'แก้ไข',
                            'deleteLabel' => 'ลบ',
                            'deleteConfirm' => 'ลบนักบำบัด '.$t->name.'?',
                        ])
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-10 text-center text-slate-500">ไม่พบนักบำบัด</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@include('admin.partials.pagination', ['paginator' => $therapists])
@endsection
