<p>สวัสดี <?php echo e($appointment->customer_name); ?>,</p>
<p>การจอง <?php echo e($appointment->id); ?> ถูกยกเลิกแล้ว</p>
<?php if($appointment->refund_amount): ?>
<p>นโยบายคืนเงิน: ฿<?php echo e(number_format($appointment->refund_amount)); ?> (<?php echo e($appointment->refund_status); ?>)</p>
<?php endif; ?>
<?php /**PATH C:\Users\USER\Booking\backend\resources\views/emails/appointment-cancelled.blade.php ENDPATH**/ ?>