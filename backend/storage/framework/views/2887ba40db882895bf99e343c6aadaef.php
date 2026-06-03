<?php $__env->startSection('title', 'แดชบอร์ด'); ?>

<?php $__env->startSection('content'); ?>
<h1 class="text-xl sm:text-2xl font-semibold tracking-tight text-slate-900 mb-5 sm:mb-6 flex items-center gap-2.5">
    <span class="inline-flex size-8 sm:size-9 items-center justify-center rounded-lg bg-slate-900 text-white shrink-0">
        <i data-lucide="layout-dashboard" class="size-[18px]"></i>
    </span>
    <span>แดชบอร์ด</span>
</h1>

<div class="grid grid-cols-1 xs:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6 sm:mb-8">
    <?php $__currentLoopData = [
        ['icon' => 'building-2', 'label' => 'คลินิก', 'value' => $stats['clinics'], 'iconBg' => 'bg-blue-500/10', 'iconText' => 'text-blue-600'],
        ['icon' => 'clipboard-list', 'label' => 'บริการ', 'value' => $stats['services'], 'iconBg' => 'bg-violet-500/10', 'iconText' => 'text-violet-600'],
        ['icon' => 'user-round', 'label' => 'นักบำบัด', 'value' => $stats['therapists'], 'iconBg' => 'bg-indigo-500/10', 'iconText' => 'text-indigo-600'],
        ['icon' => 'calendar-days', 'label' => 'การจอง', 'value' => $stats['appointments'], 'iconBg' => 'bg-slate-500/10', 'iconText' => 'text-slate-600'],
        ['icon' => 'users', 'label' => 'ลูกค้า', 'value' => $stats['customers'], 'iconBg' => 'bg-sky-500/10', 'iconText' => 'text-sky-600'],
        ['icon' => 'tag', 'label' => 'โปรโมชั่น', 'value' => $stats['promotions'], 'iconBg' => 'bg-rose-500/10', 'iconText' => 'text-rose-600'],
        ['icon' => 'clock', 'label' => 'รอชำระ', 'value' => $stats['pending'], 'iconBg' => 'bg-amber-500/10', 'iconText' => 'text-amber-600'],
        ['icon' => 'circle-check', 'label' => 'ยืนยันแล้ว', 'value' => $stats['confirmed'], 'iconBg' => 'bg-emerald-500/10', 'iconText' => 'text-emerald-600'],
    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="group rounded-xl border border-slate-200/80 bg-white p-3.5 sm:p-4 shadow-sm transition hover:border-slate-300 hover:shadow-md">
            <div class="flex items-center gap-3">
                <span class="inline-flex size-9 sm:size-10 shrink-0 items-center justify-center rounded-lg <?php echo e($card['iconBg']); ?> <?php echo e($card['iconText']); ?> ring-1 ring-inset ring-black/[0.04]">
                    <i data-lucide="<?php echo e($card['icon']); ?>" class="size-[18px] sm:size-5"></i>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-medium text-slate-500 truncate"><?php echo e($card['label']); ?></p>
                    <p class="text-xl sm:text-2xl font-semibold tabular-nums tracking-tight text-slate-900"><?php echo e(number_format($card['value'])); ?></p>
                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-6 sm:mb-8">
    <div class="rounded-xl border border-slate-200/80 bg-white p-4 sm:p-5 shadow-sm">
        <p class="font-medium text-slate-700 mb-2 flex items-center gap-2 text-sm sm:text-base">
            <span class="inline-flex size-8 items-center justify-center rounded-md bg-emerald-500/10 text-emerald-600 shrink-0">
                <i data-lucide="banknote" class="size-4"></i>
            </span>
            <span class="min-w-0">รายได้ (ยืนยันแล้ว)</span>
        </p>
        <p class="text-2xl sm:text-3xl font-semibold tabular-nums tracking-tight text-emerald-600 break-all">฿<?php echo e(number_format($stats['revenue'])); ?></p>
        <a href="<?php echo e(route('admin.reports.export')); ?>" class="inline-flex mt-3 text-sm text-emerald-700 font-medium hover:underline">
            ↓ Export CSV (รายการจอง 30 วัน)
        </a>
    </div>
    <div class="rounded-xl border border-slate-200/80 bg-white p-4 sm:p-5 shadow-sm">
        <p class="font-medium text-slate-700 mb-3 flex items-center gap-2 text-sm sm:text-base">
            <span class="inline-flex size-8 items-center justify-center rounded-md bg-slate-500/10 text-slate-600 shrink-0">
                <i data-lucide="pie-chart" class="size-4"></i>
            </span>
            สถานะการจอง
        </p>
        <div class="relative w-full h-44 sm:h-52 max-w-sm mx-auto lg:max-w-none">
            <canvas id="statusChart"></canvas>
        </div>
    </div>
</div>

<div class="rounded-xl border border-slate-200/80 bg-white p-4 sm:p-5 shadow-sm mb-6 sm:mb-8">
    <p class="font-medium text-slate-700 mb-3 flex items-center gap-2 text-sm sm:text-base">
        <span class="inline-flex size-8 items-center justify-center rounded-md bg-emerald-500/10 text-emerald-600 shrink-0">
            <i data-lucide="bar-chart-3" class="size-4"></i>
        </span>
        การจอง 7 วันล่าสุด
    </p>
    <div class="relative w-full h-48 sm:h-56">
        <canvas id="bookingChart"></canvas>
    </div>
</div>

<h2 class="text-sm sm:text-base font-semibold text-slate-900 mb-3 flex items-center gap-2">
    <i data-lucide="list-ordered" class="size-4 text-slate-500 shrink-0"></i>
    การจองล่าสุด
</h2>


<div class="md:hidden space-y-3 mb-4">
    <?php $__empty_1 = true; $__currentLoopData = $recent; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between gap-2 mb-2">
                <p class="font-mono text-xs text-slate-500"><?php echo e(Str::limit($a->id, 12)); ?></p>
                <?php echo $__env->make('admin.partials.appointment-status', ['status' => $a->status], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <p class="font-medium text-slate-900 truncate"><?php echo e($a->customer_name); ?></p>
            <p class="text-lg font-semibold tabular-nums text-slate-900 mt-1">฿<?php echo e(number_format($a->amount)); ?></p>
            <div class="mt-3">
                <?php echo $__env->make('admin.partials.table-actions', [
                    'editUrl' => route('admin.appointments.edit', $a),
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-500 text-sm">
            ยังไม่มีการจอง
        </div>
    <?php endif; ?>
</div>


<div class="hidden md:block rounded-xl border border-slate-200/80 bg-white overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[640px]">
            <thead class="bg-slate-50/80 text-left border-b border-slate-200">
                <tr>
                    <th class="px-4 lg:px-5 py-3 font-medium text-slate-600 whitespace-nowrap">เลขที่</th>
                    <th class="px-4 lg:px-5 py-3 font-medium text-slate-600">ลูกค้า</th>
                    <th class="px-4 lg:px-5 py-3 font-medium text-slate-600 whitespace-nowrap">ยอด</th>
                    <th class="px-4 lg:px-5 py-3 font-medium text-slate-600">สถานะ</th>
                    <th class="px-4 lg:px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php $__empty_1 = true; $__currentLoopData = $recent; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-4 lg:px-5 py-3 font-mono text-xs text-slate-600 whitespace-nowrap"><?php echo e(Str::limit($a->id, 8)); ?></td>
                        <td class="px-4 lg:px-5 py-3 text-slate-900 max-w-[200px] truncate"><?php echo e($a->customer_name); ?></td>
                        <td class="px-4 lg:px-5 py-3 tabular-nums text-slate-900 whitespace-nowrap">฿<?php echo e(number_format($a->amount)); ?></td>
                        <td class="px-4 lg:px-5 py-3">
                            <?php echo $__env->make('admin.partials.appointment-status', ['status' => $a->status], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </td>
                        <td class="px-4 lg:px-5 py-3 text-right whitespace-nowrap">
                            <?php echo $__env->make('admin.partials.table-actions', [
                                'editUrl' => route('admin.appointments.edit', $a),
                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">ยังไม่มีการจอง</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const chartDefaults = {
    responsive: true,
    maintainAspectRatio: false
};

const statusLabels = <?php echo json_encode($statusCounts->keys(), 15, 512) ?>;
const statusData = <?php echo json_encode($statusCounts->values(), 15, 512) ?>;
if (statusLabels.length) {
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{ data: statusData, backgroundColor: ['#f59e0b','#10b981','#6366f1','#ef4444','#94a3b8'], borderWidth: 0 }]
        },
        options: {
            ...chartDefaults,
            plugins: {
                legend: {
                    position: window.innerWidth < 640 ? 'bottom' : 'right',
                    labels: { boxWidth: 12, padding: 12, font: { size: 11 } }
                }
            },
            cutout: '62%'
        }
    });
}
new Chart(document.getElementById('bookingChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($chartLabels, 15, 512) ?>,
        datasets: [{ label: 'การจอง', data: <?php echo json_encode($chartData, 15, 512) ?>, backgroundColor: '#10b981', borderRadius: 6, borderSkipped: false }]
    },
    options: {
        ...chartDefaults,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 10 }, maxRotation: 45, minRotation: 0 } },
            y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } }, grid: { color: '#f1f5f9' } }
        }
    }
});
if (window.lucide) lucide.createIcons();
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\USER\Booking\backend\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>