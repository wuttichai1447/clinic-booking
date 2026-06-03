<p>สวัสดี {{ $appointment->customer_name }},</p>
<p>เลื่อนนัดสำเร็จ</p>
<ul>
    <li>เลขที่: {{ $appointment->id }}</li>
    <li>วันเวลาใหม่: {{ $appointment->date->format('d/m/Y') }} {{ str_replace('-', ':', $appointment->time_slot_id) }}</li>
</ul>
