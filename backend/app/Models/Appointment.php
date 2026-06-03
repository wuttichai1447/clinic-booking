<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appointment extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id', 'clinic_id', 'service_id', 'therapist_id', 'date', 'time_slot_id',
        'customer_name', 'customer_phone', 'customer_email', 'notes',
        'status', 'amount', 'subtotal', 'discount_amount', 'promotion_id',
        'payment_method', 'paid_at', 'partner_reference',
        'slot_locked_until', 'cancelled_at', 'cancel_reason', 'refund_status', 'refund_amount',
        'rescheduled_from_date', 'rescheduled_from_time_slot_id',
        'reminded_1d_at', 'reminded_2h_at',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'integer',
        'subtotal' => 'integer',
        'discount_amount' => 'integer',
        'paid_at' => 'datetime',
        'slot_locked_until' => 'datetime',
        'cancelled_at' => 'datetime',
        'refund_amount' => 'integer',
        'rescheduled_from_date' => 'date',
        'reminded_1d_at' => 'datetime',
        'reminded_2h_at' => 'datetime',
    ];

    public function startsAt(): \Carbon\Carbon
    {
        return \Carbon\Carbon::parse(
            $this->date->format('Y-m-d').' '.str_replace('-', ':', $this->time_slot_id)
        );
    }

    /** @return array<string, string> */
    public static function statusLabels(): array
    {
        return [
            'pending' => 'รอดำเนินการ',
            'awaiting_payment' => 'รอชำระเงิน',
            'awaiting_verification' => 'รอตรวจสอบ',
            'confirmed' => 'ยืนยันแล้ว',
            'cancelled' => 'ยกเลิก',
            'completed' => 'เสร็จสิ้น',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    public static function statusBadgeClass(string $status): string
    {
        return match ($status) {
            'confirmed' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
            'awaiting_payment' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
            'awaiting_verification' => 'bg-orange-50 text-orange-700 ring-orange-600/20',
            'cancelled' => 'bg-red-50 text-red-700 ring-red-600/20',
            'completed' => 'bg-slate-100 text-slate-700 ring-slate-500/20',
            'pending' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
            default => 'bg-slate-100 text-slate-600 ring-slate-500/10',
        };
    }

   

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function therapist(): BelongsTo
    {
        return $this->belongsTo(Therapist::class);
    }

    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class, 'time_slot_id');
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function toApiArray(): array
    {
        $data = [
            'id' => $this->id,
            'clinicId' => $this->clinic_id,
            'serviceId' => $this->service_id,
            'therapistId' => $this->therapist_id,
            'date' => $this->date->format('Y-m-d'),
            'timeSlotId' => $this->time_slot_id,
            'customerName' => $this->customer_name,
            'customerPhone' => $this->customer_phone,
            'customerEmail' => $this->customer_email,
            'notes' => $this->notes,
            'status' => $this->status,
            'subtotal' => $this->subtotal ?: $this->amount,
            'discountAmount' => $this->discount_amount,
            'amount' => $this->amount,
            'promotionId' => $this->promotion_id,
            'promotionCode' => $this->relationLoaded('promotion') ? $this->promotion?->code : null,
            'paymentMethod' => $this->payment_method,
            'paidAt' => $this->paid_at?->toIso8601String(),
            'createdAt' => $this->created_at?->toIso8601String(),
            'refundStatus' => $this->refund_status,
            'refundAmount' => $this->refund_amount,
            'cancelledAt' => $this->cancelled_at?->toIso8601String(),
            'cancelReason' => $this->cancel_reason,
        ];

        if ($this->relationLoaded('clinic') && $this->clinic) {
            $data['clinicName'] = $this->clinic->name;
        }
        if ($this->relationLoaded('service') && $this->service) {
            $data['serviceName'] = $this->service->name;
        }
        if ($this->relationLoaded('therapist') && $this->therapist) {
            $data['therapistName'] = $this->therapist->name;
        }

        return $data;
    }
}
