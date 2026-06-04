@extends('admin.guest-layout')

@section('title', 'เข้าสู่ระบบ')

@section('content')
<div class="max-w-md mx-auto mt-8 sm:mt-16 px-1">
    <div class="bg-white rounded-2xl shadow-lg p-6 sm:p-8 border border-slate-200">
        <h1 class="text-xl sm:text-2xl font-bold mb-2">เข้าสู่ระบบแอดมิน</h1>
        <p class="text-slate-500 text-sm mb-2">บัญชีแอดมิน (ไม่ใช่บัญชีลูกค้า)</p>
        <p class="text-slate-500 text-xs mb-6 font-mono">admin@booking.local / password</p>
        <p class="text-amber-700 text-xs mb-4 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
            เปิดที่ <strong>http://127.0.0.1:8000/admin/login</strong> เท่านั้น (อย่าใช้ <code>localhost</code> สลับกัน — จะได้ 419)
        </p>

        <form method="POST" action="/admin/login" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">อีเมล</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">รหัสผ่าน</label>
                <input type="password" name="password" required
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="remember" value="1">
                จดจำการเข้าสู่ระบบ
            </label>
            <button type="submit"
                    class="w-full inline-flex justify-center items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2.5 rounded-lg transition">
                <i data-lucide="log-in" class="size-4 shrink-0" aria-hidden="true"></i>
                เข้าสู่ระบบแอดมิน
            </button>
        </form>
    </div>
</div>
@endsection
