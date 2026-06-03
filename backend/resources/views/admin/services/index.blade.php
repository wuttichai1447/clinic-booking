@extends('admin.layout')
@section('title', 'บริการ')
@section('content')
<div class="flex flex-wrap justify-between items-center gap-4 mb-6">
    <h1 class="text-2xl font-bold">บริการ</h1>
    <a href="{{ route('admin.services.create') }}" class="bg-emerald-600 text-white px-4 py-2 rounded-lg">+ เพิ่ม</a>
</div>
<form method="GET" class="mb-4 flex gap-2 items-end">
    <div>
        <label class="text-sm text-slate-600">คลินิก</label>
        <select name="clinic_id" class="rounded-lg border px-3 py-2" onchange="this.form.submit()">
            <option value="">ทั้งหมด</option>
            @foreach ($clinics as $c)
                <option value="{{ $c->id }}" @selected($clinicId == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
    </div>
</form>
<div class="bg-white rounded-xl border overflow-hidden shadow-sm">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left">
            <tr><th class="px-4 py-3">ชื่อ</th><th class="px-4 py-3">คลินิก</th><th class="px-4 py-3">ราคา</th><th class="px-4 py-3"></th></tr>
        </thead>
        <tbody>
            @forelse ($services as $s)
                <tr class="border-t">
                    <td class="px-4 py-3">{{ $s->name }}</td>
                    <td class="px-4 py-3">{{ $s->clinic?->name }}</td>
                    <td class="px-4 py-3">฿{{ number_format($s->price) }}</td>
                    <td class="px-4 py-3 text-right">
                        @include('admin.partials.table-actions', [
                            'editUrl' => route('admin.services.edit', $s),
                            'deleteUrl' => route('admin.services.destroy', $s),
                        ])
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-10 text-center text-slate-500">ไม่พบบริการ</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@include('admin.partials.pagination', ['paginator' => $services])
@endsection
