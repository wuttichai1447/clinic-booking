<?php
    /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator */
?>
<?php if($paginator->total() > 0): ?>
    <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white rounded-xl border px-4 py-3 shadow-sm">
        <p class="text-sm text-slate-600 text-center sm:text-left">
            แสดง
            <span class="font-medium text-slate-900"><?php echo e($paginator->firstItem()); ?></span>–<span class="font-medium text-slate-900"><?php echo e($paginator->lastItem()); ?></span>
            จาก <span class="font-medium text-slate-900"><?php echo e(number_format($paginator->total())); ?></span> รายการ
            <?php if($paginator->hasPages()): ?>
                · หน้า <?php echo e($paginator->currentPage()); ?>/<?php echo e($paginator->lastPage()); ?>

            <?php endif; ?>
        </p>
        <?php echo e($paginator->links('vendor.pagination.admin')); ?>

    </div>
<?php endif; ?>
<?php /**PATH C:\Users\USER\Booking\backend\resources\views/admin/partials/pagination.blade.php ENDPATH**/ ?>