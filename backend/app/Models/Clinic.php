<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clinic extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['id', 'name', 'address', 'phone', 'image', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function therapists(): HasMany
    {
        return $this->hasMany(Therapist::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }
}
