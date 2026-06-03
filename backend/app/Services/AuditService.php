<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditService
{
    public function log(
        string $action,
        ?User $user = null,
        ?Model $subject = null,
        ?Request $request = null,
        array $meta = []
    ): void {
        AuditLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'ip' => $request?->ip(),
            'meta' => $meta ?: null,
        ]);
    }
}
