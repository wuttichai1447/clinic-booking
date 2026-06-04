@extends('admin.layout')
@section('title', $clinic->exists ? 'แก้ไขคลินิก' : 'เพิ่มคลินิก')
@section('content')
<h1 class="text-2xl font-bold mb-6">@yield('title')</h1>
<form method="POST" action="{{ $clinic->exists ? route('admin.clinics.update', $clinic) : route('admin.clinics.store') }}"
      class="bg-white rounded-xl border p-6 max-w-xl space-y-4 shadow-sm">
    @csrf
    @if ($clinic->exists) @method('PUT') @endif
    <div>
        <label class="block text-sm font-medium mb-1">รหัส (id)</label>
        <input name="id" value="{{ old('id', $clinic->id) }}"
               class="w-full rounded-lg border px-3 py-2 {{ $clinic->exists ? 'bg-slate-100' : '' }}"
               {{ $clinic->exists ? 'readonly' : '' }} required placeholder="clinic-4">
    </div>
    <div><label class="block text-sm font-medium mb-1">ชื่อ</label>
        <input name="name" value="{{ old('name', $clinic->name) }}" class="w-full rounded-lg border px-3 py-2" required></div>
    <div><label class="block text-sm font-medium mb-1">ที่อยู่</label>
        <textarea name="address" class="w-full rounded-lg border px-3 py-2" rows="2" required>{{ old('address', $clinic->address) }}</textarea></div>
    <div><label class="block text-sm font-medium mb-1">โทรศัพท์</label>
        <input name="phone" value="{{ old('phone', $clinic->phone) }}" class="w-full rounded-lg border px-3 py-2" required></div>
    <div><label class="block text-sm font-medium mb-1">รูป (URL)</label>
        <input name="image" value="{{ old('image', $clinic->image) }}" class="w-full rounded-lg border px-3 py-2" type="url"></div>
    <label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $clinic->is_active) ? 'checked' : '' }}> เปิดใช้งาน</label>
    @include('admin.partials.form-actions', [
        'cancelUrl' => route('admin.clinics.index'),
        'submitLabel' => $clinic->exists ? 'บันทึกการแก้ไข' : 'บันทึกคลินิก',
        'cancelLabel' => 'ยกเลิกและกลับรายการคลินิก',
    ])
</form>
@endsection
