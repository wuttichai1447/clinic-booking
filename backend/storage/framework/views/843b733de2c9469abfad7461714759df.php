<p>สวัสดี <?php echo e($appointment->customer_name); ?>,</p>
<p>การจองของคุณสำเร็จแล้ว — กรุณาชำระเงินเพื่อยืนยันนัด</p>
<ul>
    <li>เลขที่: <?php echo e($appointment->id); ?></li>
    <li>วันที่: <?php echo e($appointment->date->format('d/m/Y')); ?> เวลา <?php echo e(str_replace('-', ':', $appointment->time_slot_id)); ?></li>
    <li>ยอด: ฿<?php echo e(number_format($appointment->amount)); ?></li>
</ul>
<?php /**PATH C:\Users\USER\Booking\backend\resources\views/emails/booking-created.blade.php ENDPATH**/ ?>