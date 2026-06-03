<?php

namespace App\Http\Concerns;

use App\Models\User;
use Illuminate\Http\Request;

trait ResolvesApiUser
{
    /** อ่าน Sanctum token บน route ที่ไม่มี middleware auth:sanctum */
    protected function apiUser(Request $request): ?User
    {
        $user = $request->user('sanctum');

        return $user instanceof User ? $user : null;
    }

    protected function normalizePhone(?string $phone): ?string
    {
        if (! filled($phone)) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $phone);
        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '66') && strlen($digits) >= 11) {
            return '0'.substr($digits, 2);
        }

        return $digits;
    }

    /** ผูกการจองเก่าที่ไม่มี user_id แต่เบอร์/อีเมลตรงกับบัญชีลูกค้า */
    protected function linkOrphanAppointmentsToUser(User $user): void
    {
        if (! $user->isCustomer()) {
            return;
        }

        $phones = array_values(array_unique(array_filter([
            $user->phone,
            $this->normalizePhone($user->phone),
        ])));

        if ($phones === [] && ! filled($user->email)) {
            return;
        }

        $query = \App\Models\Appointment::query()->whereNull('user_id');

        $query->where(function ($q) use ($user, $phones) {
            if ($phones !== []) {
                $q->whereIn('customer_phone', $phones);
            }
            if (filled($user->email)) {
                $phones === []
                    ? $q->where('customer_email', $user->email)
                    : $q->orWhere('customer_email', $user->email);
            }
        });

        $query->update(['user_id' => $user->id]);
    }
}
