<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffLeave extends Model
{
    use HasUuids;

    public const TYPES = [
        'annual' => 'ลาพักร้อน',
        'sick' => 'ลาป่วย',
        'personal' => 'ลากิจ',
        'other' => 'อื่นๆ',
    ];

    protected $fillable = [
        'therapist_id',
        'start_date',
        'end_date',
        'leave_type',
        'note',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function therapist(): BelongsTo
    {
        return $this->belongsTo(Therapist::class);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->leave_type] ?? $this->leave_type;
    }

    public function coversDate(string $date): bool
    {
        return $this->start_date->format('Y-m-d') <= $date
            && $this->end_date->format('Y-m-d') >= $date;
    }
}
