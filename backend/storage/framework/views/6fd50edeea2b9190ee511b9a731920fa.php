<?php $__env->startSection('title', $therapist->exists ? 'แก้ไขนักบำบัด' : 'เพิ่มนักบำบัด'); ?>
<?php $__env->startSection('content'); ?>
<form method="POST" action="<?php echo e($therapist->exists ? route('admin.therapists.update', $therapist) : route('admin.therapists.store')); ?>"
      enctype="multipart/form-data"
      class="bg-white rounded-xl border p-4 sm:p-6 w-full max-w-xl space-y-4 shadow-sm">
    <?php echo csrf_field(); ?> <?php if($therapist->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>
    <div><label class="text-sm font-medium">รหัส</label>
        <input name="id" value="<?php echo e(old('id', $therapist->id)); ?>" <?php echo e($therapist->exists ? 'readonly' : ''); ?> class="w-full border rounded-lg px-3 py-2 bg-slate-50" required></div>
    <div><label class="text-sm font-medium">คลินิก</label>
        <select name="clinic_id" class="w-full border rounded-lg px-3 py-2" required>
            <?php $__currentLoopData = $clinics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($c->id); ?>" <?php if(old('clinic_id', $therapist->clinic_id) == $c->id): echo 'selected'; endif; ?>><?php echo e($c->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select></div>
    <div><label class="text-sm font-medium">ชื่อ</label>
        <input name="name" value="<?php echo e(old('name', $therapist->name)); ?>" class="w-full border rounded-lg px-3 py-2" required></div>
    <div><label class="text-sm font-medium">ความเชี่ยวชาญ</label>
        <input name="specialty" value="<?php echo e(old('specialty', $therapist->specialty)); ?>" class="w-full border rounded-lg px-3 py-2" required></div>
    <div>
        <label class="text-sm font-medium">รูปภาพ (อัปโหลดจากเครื่อง)</label>
        <input type="file" name="image_file" accept="image/*" class="w-full border rounded-lg px-3 py-2 bg-white">
        <?php if($therapist->image): ?>
            <img src="<?php echo e(str_starts_with($therapist->image, 'http') ? $therapist->image : asset($therapist->image)); ?>" alt="" class="mt-2 h-24 rounded-lg object-cover border">
        <?php endif; ?>
    </div>
    <div><label class="text-sm font-medium">หรือ รูป URL</label>
        <input name="image" value="<?php echo e(old('image', $therapist->image)); ?>" class="w-full border rounded-lg px-3 py-2" type="url" placeholder="https://..."></div>
    <label class="flex gap-2"><input type="checkbox" name="is_active" value="1" <?php echo e(old('is_active', $therapist->is_active) ? 'checked' : ''); ?>> เปิดใช้งาน</label>
    <button type="submit" class="bg-emerald-600 text-white px-6 py-2 rounded-lg">บันทึก</button>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\USER\Booking\backend\resources\views/admin/therapists/form.blade.php ENDPATH**/ ?>