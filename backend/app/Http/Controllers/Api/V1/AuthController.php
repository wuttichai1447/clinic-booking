<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone',
            'password' => ['required', 'confirmed', Password::defaults()],
            'pdpaAccepted' => 'accepted',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $data['password'],
            'role' => 'customer',
            'pdpa_accepted_at' => now(),
        ]);

        return $this->tokenResponse($user, 'สมัครสมาชิกสำเร็จ', 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['อีเมลหรือรหัสผ่านไม่ถูกต้อง'],
            ]);
        }

        if ($user->role !== 'customer') {
            throw ValidationException::withMessages([
                'email' => ['บัญชีนี้เป็นบัญชีเจ้าหน้าที่ กรุณาเข้าแอดมินที่ /admin/login'],
            ]);
        }

        $user->tokens()->where('name', 'customer')->delete();

        return $this->tokenResponse($user);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->formatUser($request->user()),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'ออกจากระบบแล้ว']);
    }

    private function tokenResponse(User $user, ?string $message = null, int $status = 200): JsonResponse
    {
        $token = $user->createToken('customer')->plainTextToken;

        return response()->json([
            'message' => $message,
            'token' => $token,
            'user' => $this->formatUser($user),
        ], $status);
    }

    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'pdpaAcceptedAt' => $user->pdpa_accepted_at?->toIso8601String(),
        ];
    }
}
