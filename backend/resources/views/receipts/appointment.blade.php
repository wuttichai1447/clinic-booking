<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <title>ใบเสร็จ {{ $appointment->id }}</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; color: #111; max-width: 600px; margin: 24px auto; }
        h1 { font-size: 18px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        td, th { border: 1px solid #ccc; padding: 8px; text-align: left; }
        .total { font-weight: bold; font-size: 16px; }
    </style>
</head>
<body>
    <h1>ใบเสร็จรับเงิน / Receipt</h1>
    <p>เลขที่: <strong>{{ $appointment->id }}</strong></p>
    <p>วันที่ออก: {{ now()->format('d/m/Y H:i') }}</p>
    <p>ลูกค้า: {{ $appointment->customer_name }} ({{ $appointment->customer_phone }})</p>
    <table>
        <tr><th>รายการ</th><th>จำนวนเงิน (THB)</th></tr>
        <tr><td>{{ $appointment->service?->name ?? 'บริการ' }}</td><td>{{ number_format($appointment->subtotal ?: $appointment->amount) }}</td></tr>
        @if($appointment->discount_amount > 0)
        <tr><td>ส่วนลด</td><td>-{{ number_format($appointment->discount_amount) }}</td></tr>
        @endif
        <tr class="total"><td>ยอดสุทธิ</td><td>฿{{ number_format($appointment->amount) }}</td></tr>
    </table>
    <p>นัด: {{ $appointment->date->format('d/m/Y') }} {{ str_replace('-', ':', $appointment->time_slot_id) }} น.</p>
    <p>สถานะ: {{ $appointment->status }} @if($appointment->paid_at) | ชำระเมื่อ {{ $appointment->paid_at->format('d/m/Y H:i') }}@endif</p>
</body>
</html>
