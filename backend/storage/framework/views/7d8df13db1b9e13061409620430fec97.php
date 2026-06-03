<?php if($paginator->hasPages()): ?>
    <nav role="navigation" aria-label="Pagination" class="inline-flex flex-wrap items-center justify-center gap-1">
        <?php if($paginator->onFirstPage()): ?>
            <span class="inline-flex items-center px-3 py-2 text-sm font-medium text-slate-400 bg-white border border-slate-200 rounded-lg cursor-default">
                ก่อนหน้า
            </span>
        <?php else: ?>
            <a href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev" class="inline-flex items-center px-3 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 hover:text-slate-900 transition">
                ก่อนหน้า
            </a>
        <?php endif; ?>

        <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(is_string($element)): ?>
                <span class="inline-flex items-center px-3 py-2 text-sm font-medium text-slate-500 bg-white border border-slate-200 rounded-lg cursor-default"><?php echo e($element); ?></span>
            <?php endif; ?>

            <?php if(is_array($element)): ?>
                <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($page == $paginator->currentPage()): ?>
                        <span aria-current="page" class="inline-flex items-center min-w-[2.5rem] justify-center px-3 py-2 text-sm font-semibold text-white bg-emerald-600 border border-emerald-600 rounded-lg">
                            <?php echo e($page); ?>

                        </span>
                    <?php else: ?>
                        <a href="<?php echo e($url); ?>" class="inline-flex items-center min-w-[2.5rem] justify-center px-3 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition" aria-label="ไปหน้า <?php echo e($page); ?>">
                            <?php echo e($page); ?>

                        </a>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php if($paginator->hasMorePages()): ?>
            <a href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next" class="inline-flex items-center px-3 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 hover:text-slate-900 transition">
                ถัดไป
            </a>
        <?php else: ?>
            <span class="inline-flex items-center px-3 py-2 text-sm font-medium text-slate-400 bg-white border border-slate-200 rounded-lg cursor-default">
                ถัดไป
            </span>
        <?php endif; ?>
    </nav>
<?php endif; ?>
<?php /**PATH C:\Users\USER\Booking\backend\resources\views/vendor/pagination/admin.blade.php ENDPATH**/ ?>