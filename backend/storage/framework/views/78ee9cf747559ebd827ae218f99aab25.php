<div class="inline-flex items-center gap-1.5 shrink-0">
    <a
        href="<?php echo e($editUrl); ?>"
        class="inline-flex size-9 items-center justify-center rounded-lg text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200/70 transition"
        title="<?php echo e($editLabel ?? 'แก้ไข'); ?>"
        aria-label="<?php echo e($editLabel ?? 'แก้ไข'); ?>"
    >
        <i data-lucide="pencil" class="size-4"></i>
    </a>
    <?php if(!empty($deleteUrl)): ?>
        <form method="POST" action="<?php echo e($deleteUrl); ?>" class="inline" onsubmit="return confirm(<?php echo json_encode($deleteConfirm ?? 'ลบรายการนี้?', 15, 512) ?>)">
            <?php echo csrf_field(); ?>
            <?php echo method_field('DELETE'); ?>
            <button
                type="submit"
                class="inline-flex size-9 items-center justify-center rounded-lg text-red-700 bg-red-50 hover:bg-red-100 border border-red-200/70 transition"
                title="<?php echo e($deleteLabel ?? 'ลบ'); ?>"
                aria-label="<?php echo e($deleteLabel ?? 'ลบ'); ?>"
            >
                <i data-lucide="trash-2" class="size-4"></i>
            </button>
        </form>
    <?php endif; ?>
</div>
<?php /**PATH C:\Users\USER\Booking\backend\resources\views/admin/partials/table-actions.blade.php ENDPATH**/ ?>