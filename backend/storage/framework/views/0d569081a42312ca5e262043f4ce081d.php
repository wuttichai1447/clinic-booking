<p>สวัสดี <?php echo e($appointment->customer_name); ?>,</p>
<p>แจ้งเตือนการนัดหมายของคุณ</p>
<ul>
    <li>เลขที่: <?php echo e($appointment->id); ?></li>
    <li>คลินิก: <?php echo e($appointment->clinic?->name); ?></li>
    <li>บริการ: <?php echo e($appointment->service?->name); ?></li>
    <li>นักบำบัด: <?php echo e($appointment->therapist?->name); ?></li>
    <li>วันที่: <?php echo e($appointment->date->format('d/m/Y')); ?> เวลา <?php echo e(str_replace('-', ':', $appointment->time_slot_id)); ?></li>
</ul>
<p>หากต้องการเลื่อนหรือยกเลิก กรุณาเข้าเว็บไซต์จองคลินิก → การจองของฉัน</p>
<?php /**PATH C:\Users\USER\Booking\backend\resources\views/emails/appointment-reminder.blade.php ENDPATH**/ ?>