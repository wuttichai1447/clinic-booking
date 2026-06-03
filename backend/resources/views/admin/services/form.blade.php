@extends('admin.layout')
@section('title', $service->exists ? 'แก้ไขบริการ' : 'เพิ่มบริการ')
@section('content')
<form method="POST" action="{{ $service->exists ? route('admin.services.update', $service) : route('admin.services.store') }}"
      class="bg-white rounded-xl border p-6 max-w-xl space-y-4 shadow-sm">
    @csrf @if ($service->exists) @method('PUT') @endif
    <div><label class="text-sm font-medium">รหัส</label>
        <input name="id" value="{{ old('id', $service->id) }}" {{ $service->exists ? 'readonly class=bg-slate-100 w-full border rounded-lg px-3 py-2' : 'class=w-full border rounded-lg px-3 py-2' }} required></div>
    <div><label class="text-sm font-medium">คลินิก</label>
        <select name="clinic_id" class="w-full border rounded-lg px-3 py-2" required>
            @foreach ($clinics as $c)
                <option value="{{ $c->id }}" @selected(old('clinic_id', $service->clinic_id) == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select></div>
    <div><label class="text-sm font-medium">ชื่อบริการ</label>
        <input name="name" value="{{ old('name', $service->name) }}" class="w-full border rounded-lg px-3 py-2" required></div>
    <div class="grid grid-cols-2 gap-4">
        <div><label class="text-sm font-medium">ระยะเวลา (นาที)</label>
            <input type="number" name="duration" value="{{ old('duration', $service->duration) }}" class="w-full border rounded-lg px-3 py-2" required></div>
        <div><label class="text-sm font-medium">ราคา (บาท)</label>
            <input type="number" name="price" value="{{ old('price', $service->price) }}" class="w-full border rounded-lg px-3 py-2" required></div>
    </div>
    <div><label class="text-sm font-medium">รูป URL</label>
        <input name="image" value="{{ old('image', $service->image) }}" class="w-full border rounded-lg px-3 py-2" type="url"></div>
    <label class="flex gap-2"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $service->is_active) ? 'checked' : '' }}> เปิดใช้งาน</label>
    <button type="submit" class="bg-emerald-600 text-white px-6 py-2 rounded-lg">บันทึก</button>
    <a href="{{ route('admin.services.index') }}" class="ml-3">ยกเลิก</a>
</form>
@endsection
