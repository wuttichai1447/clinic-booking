<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <title>ใบเสร็จ <?php echo e($appointment->id); ?></title>
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
    <p>เลขที่: <strong><?php echo e($appointment->id); ?></strong></p>
    <p>วันที่ออก: <?php echo e(now()->format('d/m/Y H:i')); ?></p>
    <p>ลูกค้า: <?php echo e($appointment->customer_name); ?> (<?php echo e($appointment->customer_phone); ?>)</p>
    <table>
        <tr><th>รายการ</th><th>จำนวนเงิน (THB)</th></tr>
        <tr><td><?php echo e($appointment->service?->name ?? 'บริการ'); ?></td><td><?php echo e(number_format($appointment->subtotal ?: $appointment->amount)); ?></td></tr>
        <?php if($appointment->discount_amount > 0): ?>
        <tr><td>ส่วนลด</td><td>-<?php echo e(number_format($appointment->discount_amount)); ?></td></tr>
        <?php endif; ?>
        <tr class="total"><td>ยอดสุทธิ</td><td>฿<?php echo e(number_format($appointment->amount)); ?></td></tr>
    </table>
    <p>นัด: <?php echo e($appointment->date->format('d/m/Y')); ?> <?php echo e(str_replace('-', ':', $appointment->time_slot_id)); ?> น.</p>
    <p>สถานะ: <?php echo e($appointment->status); ?> <?php if($appointment->paid_at): ?> | ชำระเมื่อ <?php echo e($appointment->paid_at->format('d/m/Y H:i')); ?><?php endif; ?></p>
</body>
</html>
<?php /**PATH C:\Users\USER\Booking\backend\resources\views/receipts/appointment.blade.php ENDPATH**/ ?>