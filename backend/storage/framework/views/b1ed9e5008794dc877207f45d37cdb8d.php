<?php $__env->startSection('title', 'โปรโมชั่น'); ?>
<?php $__env->startSection('content'); ?>
<div class="flex flex-col xs:flex-row xs:justify-between xs:items-center gap-3 mb-5 sm:mb-6">
    <h1 class="text-xl sm:text-2xl font-semibold">โปรโมชั่น</h1>
    <a href="<?php echo e(route('admin.promotions.create')); ?>" class="inline-flex justify-center bg-emerald-600 text-white px-4 py-2.5 rounded-lg font-medium text-sm sm:text-base">+ เพิ่ม</a>
</div>
<div class="bg-white rounded-xl border overflow-x-auto shadow-sm">
    <table class="w-full text-sm min-w-[640px]">
        <thead class="bg-slate-50 text-left">
            <tr><th class="px-4 py-3">รหัส</th><th class="px-4 py-3">ชื่อ</th><th class="px-4 py-3">ส่วนลด</th><th class="px-4 py-3">ใช้แล้ว</th><th class="px-4 py-3">สถานะ</th><th></th></tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $promotions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="border-t">
                    <td class="px-4 py-3 font-mono font-bold"><?php echo e($p->code); ?></td>
                    <td class="px-4 py-3"><?php echo e($p->title); ?></td>
                    <td class="px-4 py-3"><?php echo e($p->type === 'percent' ? $p->value.'%' : '฿'.number_format($p->value)); ?></td>
                    <td class="px-4 py-3"><?php echo e($p->used_count); ?><?php echo e($p->max_uses ? '/'.$p->max_uses : ''); ?></td>
                    <td class="px-4 py-3"><?php echo e($p->is_active ? 'เปิด' : 'ปิด'); ?></td>
                    <td class="px-4 py-3 text-right">
                        <?php echo $__env->make('admin.partials.table-actions', [
                            'editUrl' => route('admin.promotions.edit', $p),
                            'deleteUrl' => route('admin.promotions.destroy', $p),
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" class="px-4 py-10 text-center text-slate-500">ไม่พบโปรโมชั่น</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php echo $__env->make('admin.partials.pagination', ['paginator' => $promotions], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\USER\Booking\backend\resources\views/admin/promotions/index.blade.php ENDPATH**/ ?>