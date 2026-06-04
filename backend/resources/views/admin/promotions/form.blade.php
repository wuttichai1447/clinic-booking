@extends('admin.layout')
@section('title', $promotion->exists ? 'แก้ไขโปรโมชั่น' : 'เพิ่มโปรโมชั่น')
@section('content')
<h1 class="text-xl sm:text-2xl font-semibold mb-5 sm:mb-6">@yield('title')</h1>
<form method="POST" action="{{ $promotion->exists ? route('admin.promotions.update', $promotion) : route('admin.promotions.store') }}" class="bg-white rounded-xl border p-6 max-w-xl space-y-4">
    @csrf @if ($promotion->exists) @method('PUT') @endif
    <div><label class="text-sm font-medium">รหัส (CODE)</label><input name="code" value="{{ old('code', $promotion->code) }}" class="w-full border rounded-lg px-3 py-2 uppercase" required></div>
    <div><label class="text-sm font-medium">ชื่อโปรโมชั่น</label><input name="title" value="{{ old('title', $promotion->title) }}" class="w-full border rounded-lg px-3 py-2" required></div>
    <div class="grid grid-cols-2 gap-4">
        <div><label class="text-sm font-medium">ประเภท</label>
            <select name="type" class="w-full border rounded-lg px-3 py-2">
                <option value="percent" @selected(old('type', $promotion->type)==='percent')>เปอร์เซ็นต์ (%)</option>
                <option value="fixed" @selected(old('type', $promotion->type)==='fixed')>ลดเป็นบาท (฿)</option>
            </select></div>
        <div><label class="text-sm font-medium">มูลค่า</label><input type="number" name="value" value="{{ old('value', $promotion->value) }}" class="w-full border rounded-lg px-3 py-2" required></div>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div><label class="text-sm font-medium">ยอดขั้นต่ำ (฿)</label><input type="number" name="min_amount" value="{{ old('min_amount', $promotion->min_amount) }}" class="w-full border rounded-lg px-3 py-2"></div>
        <div><label class="text-sm font-medium">จำกัดจำนวนครั้ง</label><input type="number" name="max_uses" value="{{ old('max_uses', $promotion->max_uses) }}" class="w-full border rounded-lg px-3 py-2" placeholder="ว่าง = ไม่จำกัด"></div>
    </div>
    <label class="flex gap-2"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $promotion->is_active) ? 'checked' : '' }}> เปิดใช้งาน</label>
    @include('admin.partials.form-actions', [
        'cancelUrl' => route('admin.promotions.index'),
        'submitLabel' => $promotion->exists ? 'บันทึกการแก้ไข' : 'บันทึกโปรโมชั่น',
        'cancelLabel' => 'ยกเลิกและกลับรายการโปรโมชั่น',
    ])
</form>
@endsection
