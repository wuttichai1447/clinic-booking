<?php $__env->startSection('title', 'บริการ'); ?>
<?php $__env->startSection('content'); ?>
<div class="flex flex-wrap justify-between items-center gap-4 mb-6">
    <h1 class="text-2xl font-bold">บริการ</h1>
    <a href="<?php echo e(route('admin.services.create')); ?>" class="bg-emerald-600 text-white px-4 py-2 rounded-lg">+ เพิ่ม</a>
</div>
<form method="GET" class="mb-4 flex gap-2 items-end">
    <div>
        <label class="text-sm text-slate-600">คลินิก</label>
        <select name="clinic_id" class="rounded-lg border px-3 py-2" onchange="this.form.submit()">
            <option value="">ทั้งหมด</option>
            <?php $__currentLoopData = $clinics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($c->id); ?>" <?php if($clinicId == $c->id): echo 'selected'; endif; ?>><?php echo e($c->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
</form>
<div class="bg-white rounded-xl border overflow-hidden shadow-sm">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left">
            <tr><th class="px-4 py-3">ชื่อ</th><th class="px-4 py-3">คลินิก</th><th class="px-4 py-3">ราคา</th><th class="px-4 py-3"></th></tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="border-t">
                    <td class="px-4 py-3"><?php echo e($s->name); ?></td>
                    <td class="px-4 py-3"><?php echo e($s->clinic?->name); ?></td>
                    <td class="px-4 py-3">฿<?php echo e(number_format($s->price)); ?></td>
                    <td class="px-4 py-3 text-right">
                        <?php echo $__env->make('admin.partials.table-actions', [
                            'editUrl' => route('admin.services.edit', $s),
                            'deleteUrl' => route('admin.services.destroy', $s),
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="4" class="px-4 py-10 text-center text-slate-500">ไม่พบบริการ</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php echo $__env->make('admin.partials.pagination', ['paginator' => $services], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\USER\Booking\backend\resources\views/admin/services/index.blade.php ENDPATH**/ ?>