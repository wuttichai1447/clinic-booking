@php
    use App\Models\Appointment;
    $label = Appointment::statusLabels()[$status] ?? $status;
    $class = Appointment::statusBadgeClass($status);
@endphp
<span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset whitespace-nowrap {{ $class }}">
    {{ $label }}
</span>
