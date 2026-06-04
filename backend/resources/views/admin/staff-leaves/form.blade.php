@extends('admin.layout')
@section('title', $leave->exists ? 'แก้ไขการลา' : 'บันทึกการลา')
@section('content')
<h1 class="text-xl sm:text-2xl font-semibold mb-5 sm:mb-6">@yield('title')</h1>
<form method="POST" action="{{ $leave->exists ? route('admin.staff-leaves.update', $leave) : route('admin.staff-leaves.store') }}" class="bg-white rounded-xl border p-6 max-w-xl space-y-4">
    @csrf
    @if ($leave->exists) @method('PUT') @endif

    <div>
        <label class="text-sm font-medium">นักบำบัด</label>
        <select name="therapist_id" class="w-full border rounded-lg px-3 py-2 mt-1" required>
            <option value="">— เลือก —</option>
            @foreach ($therapists as $t)
                <option value="{{ $t->id }}" @selected(old('therapist_id', $leave->therapist_id) === $t->id)>
                    {{ $t->name }} — {{ $t->clinic?->name ?? 'ไม่ระบุคลินิก' }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="text-sm font-medium">วันเริ่มลา</label>
            <input type="date" name="start_date" value="{{ old('start_date', $leave->start_date?->format('Y-m-d')) }}" class="w-full border rounded-lg px-3 py-2 mt-1" required>
        </div>
        <div>
            <label class="text-sm font-medium">วันสิ้นสุดลา</label>
            <input type="date" name="end_date" value="{{ old('end_date', $leave->end_date?->format('Y-m-d')) }}" class="w-full border rounded-lg px-3 py-2 mt-1" required>
        </div>
    </div>

    <div>
        <label class="text-sm font-medium">ประเภทการลา</label>
        <select name="leave_type" class="w-full border rounded-lg px-3 py-2 mt-1" required>
            @foreach ($leaveTypes as $value => $label)
                <option value="{{ $value }}" @selected(old('leave_type', $leave->leave_type) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="text-sm font-medium">หมายเหตุ (ไม่บังคับ)</label>
        <textarea name="note" rows="2" class="w-full border rounded-lg px-3 py-2 mt-1" placeholder="เช่น ลาติดตามญาติ">{{ old('note', $leave->note) }}</textarea>
    </div>

    @include('admin.partials.form-actions', [
        'cancelUrl' => route('admin.staff-leaves.index'),
        'submitLabel' => $leave->exists ? 'บันทึกการแก้ไข' : 'บันทึกการลา',
        'cancelLabel' => 'ยกเลิกและกลับรายการลา',
    ])
</form>
@endsection
