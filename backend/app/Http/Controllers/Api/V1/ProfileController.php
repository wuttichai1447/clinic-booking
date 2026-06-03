<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Concerns\ResolvesApiUser;
use App\Http\Controllers\Controller;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    use ResolvesApiUser;

    public function __construct(protected AuditService $audit) {}

    public function show(Request $request): JsonResponse
    {
        $user = $this->apiUser($request);
        if (! $user?->isCustomer()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'pdpaAcceptedAt' => $user->pdpa_accepted_at?->toIso8601String(),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $this->apiUser($request);
        if (! $user?->isCustomer()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'pdpaAccepted' => 'sometimes|boolean',
        ]);

        if (array_key_exists('name', $data)) {
            $user->name = $data['name'];
        }
        if (array_key_exists('phone', $data)) {
            $user->phone = $data['phone'];
        }
        if (! empty($data['pdpaAccepted'])) {
            $user->pdpa_accepted_at = now();
        }
        $user->save();

        $this->audit->log('profile.updated', $user, $user, $request);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'pdpaAcceptedAt' => $user->pdpa_accepted_at?->toIso8601String(),
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $user = $this->apiUser($request);
        if (! $user?->isCustomer()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'currentPassword' => 'required|string',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if (! Hash::check($data['currentPassword'], $user->password)) {
            return response()->json(['message' => 'รหัสผ่านปัจจุบันไม่ถูกต้อง'], 422);
        }

        $user->update(['password' => $data['password']]);
        $this->audit->log('profile.password_changed', $user, $user, $request);

        return response()->json(['message' => 'เปลี่ยนรหัสผ่านแล้ว']);
    }
}
