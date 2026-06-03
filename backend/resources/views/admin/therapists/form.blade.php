@extends('admin.layout')
@section('title', $therapist->exists ? 'แก้ไขนักบำบัด' : 'เพิ่มนักบำบัด')
@section('content')
<form method="POST" action="{{ $therapist->exists ? route('admin.therapists.update', $therapist) : route('admin.therapists.store') }}"
      enctype="multipart/form-data"
      class="bg-white rounded-xl border p-4 sm:p-6 w-full max-w-xl space-y-4 shadow-sm">
    @csrf @if ($therapist->exists) @method('PUT') @endif
    <div><label class="text-sm font-medium">รหัส</label>
        <input name="id" value="{{ old('id', $therapist->id) }}" {{ $therapist->exists ? 'readonly' : '' }} class="w-full border rounded-lg px-3 py-2 bg-slate-50" required></div>
    <div><label class="text-sm font-medium">คลินิก</label>
        <select name="clinic_id" class="w-full border rounded-lg px-3 py-2" required>
            @foreach ($clinics as $c)
                <option value="{{ $c->id }}" @selected(old('clinic_id', $therapist->clinic_id) == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select></div>
    <div><label class="text-sm font-medium">ชื่อ</label>
        <input name="name" value="{{ old('name', $therapist->name) }}" class="w-full border rounded-lg px-3 py-2" required></div>
    <div><label class="text-sm font-medium">ความเชี่ยวชาญ</label>
        <input name="specialty" value="{{ old('specialty', $therapist->specialty) }}" class="w-full border rounded-lg px-3 py-2" required></div>
    <div>
        <label class="text-sm font-medium">รูปภาพ (อัปโหลดจากเครื่อง)</label>
        <input type="file" name="image_file" accept="image/*" class="w-full border rounded-lg px-3 py-2 bg-white">
        @if ($therapist->image)
            <img src="{{ str_starts_with($therapist->image, 'http') ? $therapist->image : asset($therapist->image) }}" alt="" class="mt-2 h-24 rounded-lg object-cover border">
        @endif
    </div>
    <div><label class="text-sm font-medium">หรือ รูป URL</label>
        <input name="image" value="{{ old('image', $therapist->image) }}" class="w-full border rounded-lg px-3 py-2" type="url" placeholder="https://..."></div>
    <label class="flex gap-2"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $therapist->is_active) ? 'checked' : '' }}> เปิดใช้งาน</label>
    <button type="submit" class="bg-emerald-600 text-white px-6 py-2 rounded-lg">บันทึก</button>
</form>
@endsection
