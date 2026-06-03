<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?php echo $__env->yieldContent('title', 'แอดมิน'); ?> — ระบบจองคลินิก</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-900 min-h-screen">
<main class="max-w-6xl mx-auto px-4 py-8">
    <?php if($errors->any()): ?>
        <div class="mb-4 rounded-lg bg-red-100 border border-red-300 text-red-800 px-4 py-3 max-w-md mx-auto">
            <ul class="list-disc list-inside">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>
    <?php echo $__env->yieldContent('content'); ?>
</main>
</body>
</html>
<?php /**PATH C:\Users\USER\Booking\backend\resources\views/admin/guest-layout.blade.php ENDPATH**/ ?>