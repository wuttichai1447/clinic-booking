<?php $__env->startSection('title', 'นักบำบัด'); ?>
<?php $__env->startSection('content'); ?>
<div class="flex justify-between mb-6">
    <h1 class="text-2xl font-bold">นักบำบัด</h1>
    <a href="<?php echo e(route('admin.therapists.create')); ?>" class="bg-emerald-600 text-white px-4 py-2 rounded-lg">+ เพิ่ม</a>
</div>
<form method="GET" class="mb-4">
    <select name="clinic_id" class="rounded-lg border px-3 py-2" onchange="this.form.submit()">
        <option value="">ทุกคลินิก</option>
        <?php $__currentLoopData = $clinics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($c->id); ?>" <?php if($clinicId == $c->id): echo 'selected'; endif; ?>><?php echo e($c->name); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
</form>
<div class="bg-white rounded-xl border overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">ชื่อ</th><th class="px-4 py-3 text-left">คลินิก</th><th class="px-4 py-3 text-left">ความเชี่ยวชาญ</th><th class="px-4 py-3 w-24 text-right">จัดการ</th></tr></thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $therapists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="border-t">
                    <td class="px-4 py-3"><?php echo e($t->name); ?></td>
                    <td class="px-4 py-3"><?php echo e($t->clinic?->name); ?></td>
                    <td class="px-4 py-3"><?php echo e($t->specialty); ?></td>
                    <td class="px-4 py-3 text-right">
                        <?php echo $__env->make('admin.partials.table-actions', [
                            'editUrl' => route('admin.therapists.edit', $t),
                            'deleteUrl' => route('admin.therapists.destroy', $t),
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="4" class="px-4 py-10 text-center text-slate-500">ไม่พบนักบำบัด</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php echo $__env->make('admin.partials.pagination', ['paginator' => $therapists], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\USER\Booking\backend\resources\views/admin/therapists/index.blade.php ENDPATH**/ ?>