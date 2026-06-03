<?php $__env->startSection('title', 'ลานักบำบัด'); ?>
<?php $__env->startSection('content'); ?>
<div class="flex flex-col xs:flex-row xs:justify-between xs:items-center gap-3 mb-5 sm:mb-6">
    <div>
        <h1 class="text-xl sm:text-2xl font-semibold">ลานักบำบัด</h1>
        <p class="text-sm text-slate-600 mt-1">บันทึกวันลาของนักบำบัด — ช่วงวันที่ลาจะไม่แสดงเวลาว่างให้ลูกค้าจอง</p>
    </div>
    <a href="<?php echo e(route('admin.staff-leaves.create')); ?>" class="inline-flex justify-center bg-emerald-600 text-white px-4 py-2.5 rounded-lg font-medium text-sm">+ บันทึกการลา</a>
</div>

<form method="GET" class="mb-4 flex flex-wrap gap-3 items-end">
    <div>
        <label class="text-sm font-medium block mb-1">คลินิก</label>
        <select name="clinic_id" class="border rounded-lg px-3 py-2 text-sm min-w-[180px]" onchange="this.form.submit()">
            <option value="">ทุกคลินิก</option>
            <?php $__currentLoopData = $clinics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($c->id); ?>" <?php if($filterClinicId === $c->id): echo 'selected'; endif; ?>><?php echo e($c->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div>
        <label class="text-sm font-medium block mb-1">นักบำบัด</label>
        <select name="therapist_id" class="border rounded-lg px-3 py-2 text-sm min-w-[220px]" onchange="this.form.submit()">
            <option value="">ทุกคน</option>
            <?php $__currentLoopData = $therapists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($t->id); ?>" <?php if($filterTherapistId === $t->id): echo 'selected'; endif; ?>>
                    <?php echo e($t->name); ?> (<?php echo e($t->clinic?->name ?? '—'); ?>)
                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
</form>

<div class="bg-white rounded-xl border overflow-x-auto shadow-sm">
    <table class="w-full text-sm min-w-[640px]">
        <thead class="bg-slate-50 text-left">
            <tr>
                <th class="px-4 py-3">นักบำบัด</th>
                <th class="px-4 py-3">คลินิก</th>
                <th class="px-4 py-3">ช่วงวันที่</th>
                <th class="px-4 py-3">ประเภท</th>
                <th class="px-4 py-3">หมายเหตุ</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $leaves; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="border-t">
                    <td class="px-4 py-3 font-medium"><?php echo e($row->therapist?->name); ?></td>
                    <td class="px-4 py-3"><?php echo e($row->therapist?->clinic?->name ?? '—'); ?></td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <?php echo e($row->start_date->format('d/m/Y')); ?>

                        <?php if($row->end_date->format('Y-m-d') !== $row->start_date->format('Y-m-d')): ?>
                            – <?php echo e($row->end_date->format('d/m/Y')); ?>

                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3"><?php echo e($leaveTypes[$row->leave_type] ?? $row->leave_type); ?></td>
                    <td class="px-4 py-3 text-slate-600 max-w-[200px] truncate"><?php echo e($row->note ?: '—'); ?></td>
                    <td class="px-4 py-3 text-right">
                        <?php echo $__env->make('admin.partials.table-actions', [
                            'editUrl' => route('admin.staff-leaves.edit', $row),
                            'deleteUrl' => route('admin.staff-leaves.destroy', $row),
                            'deleteConfirm' => 'ลบรายการลานี้?',
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">ยังไม่มีรายการลา</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php echo $__env->make('admin.partials.pagination', ['paginator' => $leaves], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\USER\Booking\backend\resources\views/admin/staff-leaves/index.blade.php ENDPATH**/ ?>