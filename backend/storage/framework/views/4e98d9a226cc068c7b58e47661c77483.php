<?php $__env->startSection('title', $promotion->exists ? 'แก้ไขโปรโมชั่น' : 'เพิ่มโปรโมชั่น'); ?>
<?php $__env->startSection('content'); ?>
<form method="POST" action="<?php echo e($promotion->exists ? route('admin.promotions.update', $promotion) : route('admin.promotions.store')); ?>" class="bg-white rounded-xl border p-6 max-w-xl space-y-4">
    <?php echo csrf_field(); ?> <?php if($promotion->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>
    <div><label class="text-sm font-medium">รหัส (CODE)</label><input name="code" value="<?php echo e(old('code', $promotion->code)); ?>" class="w-full border rounded-lg px-3 py-2 uppercase" required></div>
    <div><label class="text-sm font-medium">ชื่อโปรโมชั่น</label><input name="title" value="<?php echo e(old('title', $promotion->title)); ?>" class="w-full border rounded-lg px-3 py-2" required></div>
    <div class="grid grid-cols-2 gap-4">
        <div><label class="text-sm font-medium">ประเภท</label>
            <select name="type" class="w-full border rounded-lg px-3 py-2">
                <option value="percent" <?php if(old('type', $promotion->type)==='percent'): echo 'selected'; endif; ?>>เปอร์เซ็นต์ (%)</option>
                <option value="fixed" <?php if(old('type', $promotion->type)==='fixed'): echo 'selected'; endif; ?>>ลดเป็นบาท (฿)</option>
            </select></div>
        <div><label class="text-sm font-medium">มูลค่า</label><input type="number" name="value" value="<?php echo e(old('value', $promotion->value)); ?>" class="w-full border rounded-lg px-3 py-2" required></div>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div><label class="text-sm font-medium">ยอดขั้นต่ำ (฿)</label><input type="number" name="min_amount" value="<?php echo e(old('min_amount', $promotion->min_amount)); ?>" class="w-full border rounded-lg px-3 py-2"></div>
        <div><label class="text-sm font-medium">จำกัดจำนวนครั้ง</label><input type="number" name="max_uses" value="<?php echo e(old('max_uses', $promotion->max_uses)); ?>" class="w-full border rounded-lg px-3 py-2" placeholder="ว่าง = ไม่จำกัด"></div>
    </div>
    <label class="flex gap-2"><input type="checkbox" name="is_active" value="1" <?php echo e(old('is_active', $promotion->is_active) ? 'checked' : ''); ?>> เปิดใช้งาน</label>
    <button type="submit" class="bg-emerald-600 text-white px-6 py-2 rounded-lg">บันทึก</button>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\USER\Booking\backend\resources\views/admin/promotions/form.blade.php ENDPATH**/ ?>