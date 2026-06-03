<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicHoliday extends Model
{
    use HasUuids;

    protected $fillable = ['clinic_id', 'date', 'name'];

    protected $casts = ['date' => 'date'];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }
}
