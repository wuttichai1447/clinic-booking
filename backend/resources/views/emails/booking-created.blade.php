<p>สวัสดี {{ $appointment->customer_name }},</p>
<p>การจองของคุณสำเร็จแล้ว — กรุณาชำระเงินเพื่อยืนยันนัด</p>
<ul>
    <li>เลขที่: {{ $appointment->id }}</li>
    <li>วันที่: {{ $appointment->date->format('d/m/Y') }} เวลา {{ str_replace('-', ':', $appointment->time_slot_id) }}</li>
    <li>ยอด: ฿{{ number_format($appointment->amount) }}</li>
</ul>
