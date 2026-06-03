<?php $__env->startSection('title', 'แก้ไขการจอง'); ?>
<?php $__env->startSection('content'); ?>
<h1 class="text-xl sm:text-2xl font-semibold mb-4">แก้ไขการจอง</h1>
<div class="bg-white rounded-xl border p-4 sm:p-6 mb-6 text-sm space-y-1 max-w-2xl shadow-sm">
    <p><strong>เลขที่:</strong> <span class="font-mono"><?php echo e($appointment->id); ?></span></p>
    <p><strong>ลูกค้า:</strong> <?php echo e($appointment->customer_name); ?> (<?php echo e($appointment->customer_phone); ?>)</p>
    <p><strong>คลินิก:</strong> <?php echo e($appointment->clinic?->name); ?></p>
    <p><strong>บริการ:</strong> <?php echo e($appointment->service?->name); ?> — ฿<?php echo e(number_format($appointment->amount)); ?></p>
    <p><strong>นักบำบัด:</strong> <?php echo e($appointment->therapist?->name); ?></p>
    <p><strong>วันที่:</strong> <?php echo e($appointment->date->format('d/m/Y')); ?> เวลา <?php echo e($appointment->time_slot_id); ?></p>
    <p class="flex flex-wrap items-center gap-2"><strong>สถานะ:</strong> <?php echo $__env->make('admin.partials.appointment-status', ['status' => $appointment->status], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></p>
    <?php if($appointment->paid_at): ?>
        <p class="text-emerald-700 font-medium">✓ ชำระแล้ว <?php echo e($appointment->paid_at->format('d/m/Y H:i')); ?> (<?php echo e($appointment->payment_method); ?>)</p>
    <?php elseif($appointment->status === 'awaiting_verification'): ?>
        <?php $pendingPay = $appointment->payments->where('status', 'pending_verification')->first(); ?>
        <div class="mt-3 p-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-900">
            <p class="font-medium">รอยืนยันการชำระเงิน</p>
            <?php if($pendingPay): ?>
                <p>ช่องทาง: <?php echo e($pendingPay->method); ?> · อ้างอิง: <span class="font-mono"><?php echo e($pendingPay->payment_reference); ?></span></p>
                <p>ส่งเมื่อ: <?php echo e($pendingPay->created_at->format('d/m/Y H:i')); ?></p>
                <?php if($pendingPay->proof_path): ?>
                    <p class="mt-2"><a href="<?php echo e(asset('storage/'.$pendingPay->proof_path)); ?>" target="_blank" class="text-emerald-700 underline font-medium">ดูสลิปโอนเงิน</a></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php if($appointment->status === 'awaiting_verification'): ?>
    <form method="POST" action="<?php echo e(route('admin.appointments.confirm-payment', $appointment)); ?>" class="mb-6">
        <?php echo csrf_field(); ?>
        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2.5 rounded-lg font-medium"
                onclick="return confirm('ยืนยันว่าได้รับเงินแล้ว?')">
            ✓ ยืนยันการชำระเงิน (confirmed)
        </button>
    </form>
<?php endif; ?>

<form method="POST" action="<?php echo e(route('admin.appointments.update', $appointment)); ?>" class="bg-white rounded-xl border p-4 sm:p-6 max-w-xl space-y-4 shadow-sm w-full">
    <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
    <div>
        <label class="text-sm font-medium">สถานะ</label>
        <select name="status" class="w-full border rounded-lg px-3 py-2.5 mt-1">
            <?php $__currentLoopData = \App\Models\Appointment::statusLabels(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($value); ?>" <?php if(old('status', $appointment->status) === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div>
        <label class="text-sm font-medium">หมายเหตุ</label>
        <textarea name="notes" rows="3" class="w-full border rounded-lg px-3 py-2.5 mt-1"><?php echo e(old('notes', $appointment->notes)); ?></textarea>
    </div>
    <button type="submit" class="bg-emerald-600 text-white px-6 py-2.5 rounded-lg font-medium">บันทึก</button>
    <a href="<?php echo e(route('admin.appointments.index')); ?>" class="ml-3 text-sm text-slate-600 hover:underline">กลับ</a>
</form>

<?php if(!in_array($appointment->status, ['cancelled', 'completed'])): ?>
<form method="POST" action="<?php echo e(route('admin.appointments.reschedule', $appointment)); ?>" class="bg-white rounded-xl border p-4 sm:p-6 max-w-xl space-y-4 shadow-sm w-full mt-6">
    <?php echo csrf_field(); ?>
    <h2 class="font-semibold">เลื่อนนัด</h2>
    <div class="grid sm:grid-cols-2 gap-4">
        <div>
            <label class="text-sm font-medium">วันที่ใหม่</label>
            <input type="date" name="date" required class="w-full border rounded-lg px-3 py-2.5 mt-1" value="<?php echo e($appointment->date->format('Y-m-d')); ?>">
        </div>
        <div>
            <label class="text-sm font-medium">ช่วงเวลา (id)</label>
            <input type="text" name="time_slot_id" required class="w-full border rounded-lg px-3 py-2.5 mt-1 font-mono" value="<?php echo e($appointment->time_slot_id); ?>" placeholder="09-00">
        </div>
    </div>
    <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-lg font-medium">เลื่อนนัด</button>
</form>

<form method="POST" action="<?php echo e(route('admin.appointments.cancel', $appointment)); ?>" class="mt-4" onsubmit="return confirm('ยกเลิกการจองนี้?')">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="reason" value="ยกเลิกโดยแอดมิน">
    <button type="submit" class="text-red-600 text-sm font-medium hover:underline">ยกเลิกการจอง</button>
</form>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\USER\Booking\backend\resources\views/admin/appointments/edit.blade.php ENDPATH**/ ?>