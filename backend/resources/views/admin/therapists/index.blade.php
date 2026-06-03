@extends('admin.layout')
@section('title', 'นักบำบัด')
@section('content')
<div class="flex justify-between mb-6">
    <h1 class="text-2xl font-bold">นักบำบัด</h1>
    <a href="{{ route('admin.therapists.create') }}" class="bg-emerald-600 text-white px-4 py-2 rounded-lg">+ เพิ่ม</a>
</div>
<form method="GET" class="mb-4">
    <select name="clinic_id" class="rounded-lg border px-3 py-2" onchange="this.form.submit()">
        <option value="">ทุกคลินิก</option>
        @foreach ($clinics as $c)
            <option value="{{ $c->id }}" @selected($clinicId == $c->id)>{{ $c->name }}</option>
        @endforeach
    </select>
</form>
<div class="bg-white rounded-xl border overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">ชื่อ</th><th class="px-4 py-3 text-left">คลินิก</th><th class="px-4 py-3 text-left">ความเชี่ยวชาญ</th><th class="px-4 py-3 w-24 text-right">จัดการ</th></tr></thead>
        <tbody>
            @forelse ($therapists as $t)
                <tr class="border-t">
                    <td class="px-4 py-3">{{ $t->name }}</td>
                    <td class="px-4 py-3">{{ $t->clinic?->name }}</td>
                    <td class="px-4 py-3">{{ $t->specialty }}</td>
                    <td class="px-4 py-3 text-right">
                        @include('admin.partials.table-actions', [
                            'editUrl' => route('admin.therapists.edit', $t),
                            'deleteUrl' => route('admin.therapists.destroy', $t),
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
