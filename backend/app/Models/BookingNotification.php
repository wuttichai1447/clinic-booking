<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingNotification extends Model
{
    use HasUuids;

    public const EVENT_LABELS = [
        'booking_created' => 'จองใหม่',
        'awaiting_verification' => 'รอตรวจสลิป',
        'payment_confirmed' => 'ชำระเงินแล้ว',
        'appointment_cancelled' => 'ยกเลิกการจอง',
        'appointment_rescheduled' => 'เลื่อนนัด',
    ];

    protected $fillable = [
        'appointment_id',
        'event_type',
        'title',
        'message',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function eventLabel(): string
    {
        return self::EVENT_LABELS[$this->event_type] ?? $this->event_type;
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    public function markRead(): void
    {
        if ($this->read_at === null) {
            $this->update(['read_at' => now()]);
        }
    }

    public static function record(
        string $eventType,
        string $title,
        string $message,
        ?Appointment $appointment = null,
    ): self {
        return self::create([
            'appointment_id' => $appointment?->id,
            'event_type' => $eventType,
            'title' => $title,
            'message' => $message,
        ]);
    }
}
