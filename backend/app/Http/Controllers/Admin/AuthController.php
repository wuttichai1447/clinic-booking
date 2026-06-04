<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    protected function validateCsrf(Request $request): void
    {
        $token = $request->input('_token');
        if (! is_string($token) || ! hash_equals($request->session()->token(), $token)) {
            throw ValidationException::withMessages([
                'email' => 'เซสชันหมดอายุ กรุณารีเฟรชหน้าแล้วลองใหม่',
            ]);
        }
    }

    public function showLogin(Request $request): View
    {
        return view('admin.login', [
            'adminEmailHint' => User::where('role', 'admin')->value('email'),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $this->validateCsrf($request);

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง'])->onlyInput('email');
        }

        $user = Auth::user();
        if (! in_array($user->role, ['admin', 'staff'], true)) {
            Auth::logout();

            return back()->withErrors(['email' => 'บัญชีนี้ไม่มีสิทธิ์เข้าแอดมิน'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
