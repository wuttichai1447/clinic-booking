<?php $__env->startSection('title', 'คลินิก'); ?>
<?php $__env->startSection('content'); ?>
<div class="flex flex-col xs:flex-row xs:justify-between xs:items-center gap-3 mb-5 sm:mb-6">
    <h1 class="text-xl sm:text-2xl font-semibold">คลินิก</h1>
    <a href="<?php echo e(route('admin.clinics.create')); ?>" class="inline-flex justify-center bg-emerald-600 text-white px-4 py-2.5 rounded-lg hover:bg-emerald-700 font-medium text-sm sm:text-base">+ เพิ่ม</a>
</div>
<div class="bg-white rounded-xl border overflow-x-auto shadow-sm">
    <table class="w-full text-sm min-w-[560px]">
        <thead class="bg-slate-50 text-left">
            <tr>
                <th class="px-4 py-3">ID</th>
                <th class="px-4 py-3">ชื่อ</th>
                <th class="px-4 py-3">โทร</th>
                <th class="px-4 py-3">สถานะ</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $clinics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="border-t">
                    <td class="px-4 py-3 font-mono text-xs"><?php echo e($c->id); ?></td>
                    <td class="px-4 py-3"><?php echo e($c->name); ?></td>
                    <td class="px-4 py-3"><?php echo e($c->phone); ?></td>
                    <td class="px-4 py-3"><?php echo e($c->is_active ? 'เปิด' : 'ปิด'); ?></td>
                    <td class="px-4 py-3 text-right">
                        <?php echo $__env->make('admin.partials.table-actions', [
                            'editUrl' => route('admin.clinics.edit', $c),
                            'deleteUrl' => route('admin.clinics.destroy', $c),
                            'deleteConfirm' => 'ลบคลินิกนี้?',
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">ไม่พบคลินิก</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php echo $__env->make('admin.partials.pagination', ['paginator' => $clinics], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\USER\Booking\backend\resources\views/admin/clinics/index.blade.php ENDPATH**/ ?>