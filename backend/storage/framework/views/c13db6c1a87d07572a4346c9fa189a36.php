<?php $__env->startSection('title', 'การจอง'); ?>
<?php $__env->startSection('content'); ?>
<div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-5 sm:mb-6">
    <h1 class="text-xl sm:text-2xl font-semibold">การจอง</h1>
    <a href="<?php echo e(route('admin.appointments.create')); ?>" class="inline-flex justify-center items-center gap-2 bg-emerald-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-emerald-700">
        + จองแทนลูกค้า
    </a>
</div>
<form method="GET" class="flex flex-col sm:flex-row flex-wrap gap-3 mb-4 bg-white p-4 sm:p-5 rounded-xl border shadow-sm">
    <input name="phone" value="<?php echo e($phone); ?>" placeholder="ค้นหาเบอร์โทร" class="w-full sm:flex-1 min-w-0 border rounded-lg px-3 py-2">
    <select name="status" class="w-full sm:w-auto border rounded-lg px-3 py-2">
        <option value="">ทุกสถานะ</option>
        <?php $__currentLoopData = array_keys(\App\Models\Appointment::statusLabels()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($s); ?>" <?php if($status == $s): echo 'selected'; endif; ?>><?php echo e(\App\Models\Appointment::statusLabels()[$s]); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
    <button type="submit" class="w-full sm:w-auto bg-slate-800 text-white px-4 py-2.5 rounded-lg font-medium">ค้นหา</button>
</form>
<div class="bg-white rounded-xl border overflow-x-auto shadow-sm -mx-0 sm:mx-0">
    <table class="w-full text-sm min-w-[720px]">
        <thead class="bg-slate-50 text-left">
            <tr>
                <th class="px-4 py-3">ลูกค้า</th>
                <th class="px-4 py-3">คลินิก / บริการ</th>
                <th class="px-4 py-3">วันที่ / เวลา</th>
                <th class="px-4 py-3">ยอด</th>
                <th class="px-4 py-3">สถานะ</th>
                <th class="px-4 py-3 w-24 text-right">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $appointments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="border-t">
                    <td class="px-4 py-3"><?php echo e($a->customer_name); ?><br><span class="text-slate-500"><?php echo e($a->customer_phone); ?></span></td>
                    <td class="px-4 py-3"><?php echo e($a->clinic?->name); ?><br><span class="text-slate-500"><?php echo e($a->service?->name); ?></span></td>
                    <td class="px-4 py-3"><?php echo e($a->date->format('d/m/Y')); ?> <?php echo e($a->time_slot_id); ?></td>
                    <td class="px-4 py-3">฿<?php echo e(number_format($a->amount)); ?></td>
                    <td class="px-4 py-3"><?php echo $__env->make('admin.partials.appointment-status', ['status' => $a->status], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></td>
                    <td class="px-4 py-3 text-right">
                        <?php echo $__env->make('admin.partials.table-actions', [
                            'editUrl' => route('admin.appointments.edit', $a),
                            'deleteUrl' => route('admin.appointments.destroy', $a),
                            'deleteConfirm' => 'ลบการจอง?',
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" class="px-4 py-10 text-center text-slate-500">ไม่พบการจอง</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php echo $__env->make('admin.partials.pagination', ['paginator' => $appointments], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\USER\Booking\backend\resources\views/admin/appointments/index.blade.php ENDPATH**/ ?>