<p>สวัสดี <?php echo e($appointment->customer_name); ?>,</p>
<p>ชำระเงินสำเร็จ — การจองยืนยันแล้ว</p>
<ul>
    <li>เลขที่: <?php echo e($appointment->id); ?></li>
    <li>วันที่: <?php echo e($appointment->date->format('d/m/Y')); ?> เวลา <?php echo e(str_replace('-', ':', $appointment->time_slot_id)); ?></li>
</ul>
<?php /**PATH C:\Users\USER\Booking\backend\resources\views/emails/payment-confirmed.blade.php ENDPATH**/ ?>