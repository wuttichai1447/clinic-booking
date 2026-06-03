<?php $__env->startSection('title', $leave->exists ? 'แก้ไขการลา' : 'บันทึกการลา'); ?>
<?php $__env->startSection('content'); ?>
<form method="POST" action="<?php echo e($leave->exists ? route('admin.staff-leaves.update', $leave) : route('admin.staff-leaves.store')); ?>" class="bg-white rounded-xl border p-6 max-w-xl space-y-4">
    <?php echo csrf_field(); ?>
    <?php if($leave->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

    <div>
        <label class="text-sm font-medium">พนักงาน (นักบำบัด)</label>
        <select name="therapist_id" class="w-full border rounded-lg px-3 py-2 mt-1" required>
            <option value="">— เลือก —</option>
            <?php $__currentLoopData = $therapists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($t->id); ?>" <?php if(old('therapist_id', $leave->therapist_id) === $t->id): echo 'selected'; endif; ?>>
                    <?php echo e($t->name); ?> — <?php echo e($t->clinic?->name ?? 'ไม่ระบุคลินิก'); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="text-sm font-medium">วันเริ่มลา</label>
            <input type="date" name="start_date" value="<?php echo e(old('start_date', $leave->start_date?->format('Y-m-d'))); ?>" class="w-full border rounded-lg px-3 py-2 mt-1" required>
        </div>
        <div>
            <label class="text-sm font-medium">วันสิ้นสุดลา</label>
            <input type="date" name="end_date" value="<?php echo e(old('end_date', $leave->end_date?->format('Y-m-d'))); ?>" class="w-full border rounded-lg px-3 py-2 mt-1" required>
        </div>
    </div>

    <div>
        <label class="text-sm font-medium">ประเภทการลา</label>
        <select name="leave_type" class="w-full border rounded-lg px-3 py-2 mt-1" required>
            <?php $__currentLoopData = $leaveTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($value); ?>" <?php if(old('leave_type', $leave->leave_type) === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>

    <div>
        <label class="text-sm font-medium">หมายเหตุ (ไม่บังคับ)</label>
        <textarea name="note" rows="2" class="w-full border rounded-lg px-3 py-2 mt-1" placeholder="เช่น ลาติดตามญาติ"><?php echo e(old('note', $leave->note)); ?></textarea>
    </div>

    <button type="submit" class="bg-emerald-600 text-white px-6 py-2 rounded-lg">บันทึก</button>
    <a href="<?php echo e(route('admin.staff-leaves.index')); ?>" class="ml-2 text-slate-600 text-sm">ยกเลิก</a>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\USER\Booking\backend\resources\views/admin/staff-leaves/form.blade.php ENDPATH**/ ?>