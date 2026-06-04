@extends('admin.layout')
@section('title', 'แก้ไขการจอง')
@section('content')
<h1 class="text-xl sm:text-2xl font-semibold mb-4">แก้ไขการจอง</h1>
<div class="bg-white rounded-xl border p-4 sm:p-6 mb-6 text-sm space-y-1 max-w-2xl shadow-sm">
    <p><strong>เลขที่:</strong> <span class="font-mono">{{ $appointment->id }}</span></p>
    <p><strong>ลูกค้า:</strong> {{ $appointment->customer_name }} ({{ $appointment->customer_phone }})</p>
    <p><strong>คลินิก:</strong> {{ $appointment->clinic?->name }}</p>
    <p><strong>บริการ:</strong> {{ $appointment->service?->name }} — ฿{{ number_format($appointment->amount) }}</p>
    <p><strong>นักบำบัด:</strong> {{ $appointment->therapist?->name }}</p>
    <p><strong>วันที่:</strong> {{ $appointment->date->format('d/m/Y') }} เวลา {{ $appointment->time_slot_id }}</p>
    <p class="flex flex-wrap items-center gap-2"><strong>สถานะ:</strong> @include('admin.partials.appointment-status', ['status' => $appointment->status])</p>
    @if ($appointment->paid_at)
        <p class="text-emerald-700 font-medium">✓ ชำระแล้ว {{ $appointment->paid_at->format('d/m/Y H:i') }} ({{ $appointment->payment_method }})</p>
    @elseif ($appointment->status === 'awaiting_verification')
        @php $pendingPay = $appointment->payments->where('status', 'pending_verification')->first(); @endphp
        <div class="mt-3 p-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-900">
            <p class="font-medium">รอยืนยันการชำระเงิน</p>
            @if ($pendingPay)
                <p>ช่องทาง: {{ $pendingPay->method }} · อ้างอิง: <span class="font-mono">{{ $pendingPay->payment_reference }}</span></p>
                <p>ส่งเมื่อ: {{ $pendingPay->created_at->format('d/m/Y H:i') }}</p>
                @if ($pendingPay->proof_path)
                    <p class="mt-2"><a href="{{ asset('storage/'.$pendingPay->proof_path) }}" target="_blank" class="text-emerald-700 underline font-medium">ดูสลิปโอนเงิน</a></p>
                @endif
            @endif
        </div>
    @endif
</div>

@if ($appointment->status === 'awaiting_verification')
    <form method="POST" action="{{ route('admin.appointments.confirm-payment', $appointment) }}" class="mb-6">
        @csrf
        <button type="submit" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2.5 rounded-lg font-medium"
                onclick="return confirm('ยืนยันว่าได้รับเงินแล้ว?')">
            <i data-lucide="circle-check" class="size-4 shrink-0" aria-hidden="true"></i>
            ยืนยันการชำระเงิน
        </button>
    </form>
@endif

<form method="POST" action="{{ route('admin.appointments.update', $appointment) }}" class="bg-white rounded-xl border p-4 sm:p-6 max-w-xl space-y-4 shadow-sm w-full">
    @csrf @method('PUT')
    <div>
        <label class="text-sm font-medium">สถานะ</label>
        <select name="status" class="w-full border rounded-lg px-3 py-2.5 mt-1">
            @foreach (\App\Models\Appointment::statusLabels() as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $appointment->status) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-sm font-medium">หมายเหตุ</label>
        <textarea name="notes" rows="3" class="w-full border rounded-lg px-3 py-2.5 mt-1">{{ old('notes', $appointment->notes) }}</textarea>
    </div>
    @include('admin.partials.form-actions', [
        'cancelUrl' => route('admin.appointments.index'),
        'submitLabel' => 'บันทึกการเปลี่ยนแปลง',
        'cancelLabel' => 'ยกเลิกและกลับรายการจอง',
    ])
</form>

@if (!in_array($appointment->status, ['cancelled', 'completed']))
<form method="POST" action="{{ route('admin.appointments.reschedule', $appointment) }}" class="bg-white rounded-xl border p-4 sm:p-6 max-w-xl space-y-4 shadow-sm w-full mt-6">
    @csrf
    <h2 class="font-semibold">เลื่อนนัด</h2>
    <div class="grid sm:grid-cols-2 gap-4">
        <div>
            <label class="text-sm font-medium">วันที่ใหม่</label>
            <input type="date" name="date" required class="w-full border rounded-lg px-3 py-2.5 mt-1" value="{{ $appointment->date->format('Y-m-d') }}">
        </div>
        <div>
            <label class="text-sm font-medium">ช่วงเวลา (id)</label>
            <input type="text" name="time_slot_id" required class="w-full border rounded-lg px-3 py-2.5 mt-1 font-mono" value="{{ $appointment->time_slot_id }}" placeholder="09-00">
        </div>
    </div>
    <button type="submit" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-medium">
        <i data-lucide="calendar-clock" class="size-4 shrink-0" aria-hidden="true"></i>
        ยืนยันเลื่อนนัด
    </button>
</form>

<form method="POST" action="{{ route('admin.appointments.cancel', $appointment) }}" class="mt-4" onsubmit="return confirm('ยกเลิกการจองนี้?')">
    @csrf
    <input type="hidden" name="reason" value="ยกเลิกโดยแอดมิน">
    <button type="submit" class="inline-flex items-center gap-2 text-red-700 text-sm font-medium px-4 py-2.5 rounded-lg border border-red-200 bg-red-50 hover:bg-red-100 transition">
        <i data-lucide="ban" class="size-4 shrink-0" aria-hidden="true"></i>
        ยกเลิกการจอง
    </button>
</form>
@endif
@endsection
