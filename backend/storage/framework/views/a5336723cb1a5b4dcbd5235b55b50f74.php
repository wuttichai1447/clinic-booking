<?php $__env->startSection('title', 'วันหยุดคลินิก'); ?>
<?php $__env->startSection('content'); ?>
<div class="flex flex-col xs:flex-row xs:justify-between xs:items-center gap-3 mb-5 sm:mb-6">
    <div>
        <h1 class="text-xl sm:text-2xl font-semibold">วันหยุดคลินิก</h1>
        <p class="text-sm text-slate-600 mt-1">วันที่ตรงกับรายการนี้จะไม่แสดงช่วงเวลาให้จอง (ทุกสาขา หรือเฉพาะคลินิก)</p>
    </div>
    <a href="<?php echo e(route('admin.holidays.create')); ?>" class="inline-flex justify-center bg-emerald-600 text-white px-4 py-2.5 rounded-lg font-medium text-sm">+ เพิ่มวันหยุด</a>
</div>

<form method="GET" class="mb-4 flex flex-wrap gap-2 items-end">
    <div>
        <label class="text-sm font-medium block mb-1">กรองตามคลินิก</label>
        <select name="clinic_id" class="border rounded-lg px-3 py-2 text-sm min-w-[200px]" onchange="this.form.submit()">
            <option value="">ทั้งหมด</option>
            <?php $__currentLoopData = $clinics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($c->id); ?>" <?php if($filterClinicId === $c->id): echo 'selected'; endif; ?>><?php echo e($c->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
</form>

<div class="bg-white rounded-xl border overflow-x-auto shadow-sm">
    <table class="w-full text-sm min-w-[520px]">
        <thead class="bg-slate-50 text-left">
            <tr>
                <th class="px-4 py-3">วันที่</th>
                <th class="px-4 py-3">คลินิก</th>
                <th class="px-4 py-3">ชื่อวันหยุด</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $holidays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="border-t">
                    <td class="px-4 py-3 font-medium"><?php echo e($h->date->format('d/m/Y')); ?></td>
                    <td class="px-4 py-3"><?php echo e($h->clinic?->name ?? 'ทุกสาขา'); ?></td>
                    <td class="px-4 py-3"><?php echo e($h->name ?: '—'); ?></td>
                    <td class="px-4 py-3 text-right">
                        <?php echo $__env->make('admin.partials.table-actions', [
                            'editUrl' => route('admin.holidays.edit', $h),
                            'deleteUrl' => route('admin.holidays.destroy', $h),
                            'deleteConfirm' => 'ลบวันหยุดนี้?',
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">ยังไม่มีวันหยุด</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php echo $__env->make('admin.partials.pagination', ['paginator' => $holidays], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\USER\Booking\backend\resources\views/admin/holidays/index.blade.php ENDPATH**/ ?>