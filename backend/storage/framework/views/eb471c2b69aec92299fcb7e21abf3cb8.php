<?php $__env->startSection('title', $holiday->exists ? 'แก้ไขวันหยุด' : 'เพิ่มวันหยุด'); ?>
<?php $__env->startSection('content'); ?>
<form method="POST" action="<?php echo e($holiday->exists ? route('admin.holidays.update', $holiday) : route('admin.holidays.store')); ?>" class="bg-white rounded-xl border p-6 max-w-xl space-y-4">
    <?php echo csrf_field(); ?>
    <?php if($holiday->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

    <div>
        <label class="text-sm font-medium">คลินิก</label>
        <select name="clinic_id" class="w-full border rounded-lg px-3 py-2 mt-1">
            <option value="">ทุกสาขา (วันหยุดรวม)</option>
            <?php $__currentLoopData = $clinics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($c->id); ?>" <?php if(old('clinic_id', $holiday->clinic_id) === $c->id): echo 'selected'; endif; ?>><?php echo e($c->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>

    <div>
        <label class="text-sm font-medium">วันที่</label>
        <input type="date" name="date" value="<?php echo e(old('date', $holiday->date?->format('Y-m-d'))); ?>" class="w-full border rounded-lg px-3 py-2 mt-1" required>
    </div>

    <div>
        <label class="text-sm font-medium">ชื่อวันหยุด (ไม่บังคับ)</label>
        <input name="name" value="<?php echo e(old('name', $holiday->name)); ?>" class="w-full border rounded-lg px-3 py-2 mt-1" placeholder="เช่น วันสงกรานต์">
    </div>

    <button type="submit" class="bg-emerald-600 text-white px-6 py-2 rounded-lg">บันทึก</button>
    <a href="<?php echo e(route('admin.holidays.index')); ?>" class="ml-2 text-slate-600 text-sm">ยกเลิก</a>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\USER\Booking\backend\resources\views/admin/holidays/form.blade.php ENDPATH**/ ?>