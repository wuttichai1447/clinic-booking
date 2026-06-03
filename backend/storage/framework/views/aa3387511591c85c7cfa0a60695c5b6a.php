<?php
    use App\Models\Appointment;
    $label = Appointment::statusLabels()[$status] ?? $status;
    $class = Appointment::statusBadgeClass($status);
?>
<span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset whitespace-nowrap <?php echo e($class); ?>">
    <?php echo e($label); ?>

</span>
<?php /**PATH C:\Users\USER\Booking\backend\resources\views/admin/partials/appointment-status.blade.php ENDPATH**/ ?>