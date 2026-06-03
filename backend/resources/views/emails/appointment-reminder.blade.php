<p>สวัสดี {{ $appointment->customer_name }},</p>
<p>แจ้งเตือนการนัดหมายของคุณ</p>
<ul>
    <li>เลขที่: {{ $appointment->id }}</li>
    <li>คลินิก: {{ $appointment->clinic?->name }}</li>
    <li>บริการ: {{ $appointment->service?->name }}</li>
    <li>นักบำบัด: {{ $appointment->therapist?->name }}</li>
    <li>วันที่: {{ $appointment->date->format('d/m/Y') }} เวลา {{ str_replace('-', ':', $appointment->time_slot_id) }}</li>
</ul>
<p>หากต้องการเลื่อนหรือยกเลิก กรุณาเข้าเว็บไซต์จองคลินิก → การจองของฉัน</p>
