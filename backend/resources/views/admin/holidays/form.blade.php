@extends('admin.layout')
@section('title', $holiday->exists ? 'แก้ไขวันหยุด' : 'เพิ่มวันหยุด')
@section('content')
<h1 class="text-xl sm:text-2xl font-semibold mb-5 sm:mb-6">@yield('title')</h1>
<form method="POST" action="{{ $holiday->exists ? route('admin.holidays.update', $holiday) : route('admin.holidays.store') }}" class="bg-white rounded-xl border p-6 max-w-xl space-y-4">
    @csrf
    @if ($holiday->exists) @method('PUT') @endif

    <div>
        <label class="text-sm font-medium">คลินิก</label>
        <select name="clinic_id" class="w-full border rounded-lg px-3 py-2 mt-1">
            <option value="">ทุกสาขา (วันหยุดรวม)</option>
            @foreach ($clinics as $c)
                <option value="{{ $c->id }}" @selected(old('clinic_id', $holiday->clinic_id) === $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="text-sm font-medium">วันที่</label>
        <input type="date" name="date" value="{{ old('date', $holiday->date?->format('Y-m-d')) }}" class="w-full border rounded-lg px-3 py-2 mt-1" required>
    </div>

    <div>
        <label class="text-sm font-medium">ชื่อวันหยุด (ไม่บังคับ)</label>
        <input name="name" value="{{ old('name', $holiday->name) }}" class="w-full border rounded-lg px-3 py-2 mt-1" placeholder="เช่น วันสงกรานต์">
    </div>

    @include('admin.partials.form-actions', [
        'cancelUrl' => route('admin.holidays.index'),
        'submitLabel' => $holiday->exists ? 'บันทึกการแก้ไข' : 'บันทึกวันหยุด',
        'cancelLabel' => 'ยกเลิกและกลับรายการวันหยุด',
    ])
</form>
@endsection
