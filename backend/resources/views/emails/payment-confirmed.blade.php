<p>สวัสดี {{ $appointment->customer_name }},</p>
<p>ชำระเงินสำเร็จ — การจองยืนยันแล้ว</p>
<ul>
    <li>เลขที่: {{ $appointment->id }}</li>
    <li>วันที่: {{ $appointment->date->format('d/m/Y') }} เวลา {{ str_replace('-', ':', $appointment->time_slot_id) }}</li>
</ul>
