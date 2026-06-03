<p>สวัสดี <?php echo e($appointment->customer_name); ?>,</p>
<p>เลื่อนนัดสำเร็จ</p>
<ul>
    <li>เลขที่: <?php echo e($appointment->id); ?></li>
    <li>วันเวลาใหม่: <?php echo e($appointment->date->format('d/m/Y')); ?> <?php echo e(str_replace('-', ':', $appointment->time_slot_id)); ?></li>
</ul>
<?php /**PATH C:\Users\USER\Booking\backend\resources\views/emails/appointment-rescheduled.blade.php ENDPATH**/ ?>