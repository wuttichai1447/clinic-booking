<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>@yield('title', 'แอดมิน') — ระบบจองคลินิก</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    screens: { xs: '475px' }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <style>
        [data-lucide] { stroke-width: 1.75; }
        .admin-nav-open { display: flex !important; }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 min-h-screen antialiased">
@auth
<nav class="bg-slate-900 text-white shadow-lg sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-3 py-3 min-h-[3.25rem]">
            <a href="{{ route('admin.dashboard') }}" class="font-semibold text-base sm:text-lg truncate max-w-[55vw] sm:max-w-none">
                แอดมิน — จองคลินิก
            </a>
            <div class="flex items-center gap-2 shrink-0">
                <form method="POST" action="{{ route('admin.logout') }}" class="hidden sm:inline">
                    @csrf
                    <button type="submit" class="text-sm text-slate-300 hover:text-white px-2 py-1 rounded-md hover:bg-white/10 transition">
                        ออกจากระบบ
                    </button>
                </form>
                <button
                    type="button"
                    id="admin-nav-toggle"
                    class="md:hidden inline-flex items-center justify-center size-10 rounded-lg text-slate-200 hover:bg-white/10 transition"
                    aria-expanded="false"
                    aria-controls="admin-nav-menu"
                    aria-label="เปิดเมนู"
                >
                    <i data-lucide="menu" class="size-5" id="admin-nav-icon-open"></i>
                    <i data-lucide="x" class="size-5 hidden" id="admin-nav-icon-close"></i>
                </button>
            </div>
        </div>

        <div
            id="admin-nav-menu"
            class="hidden md:flex flex-col md:flex-row md:flex-wrap md:items-center gap-1 md:gap-3 pb-3 md:pb-3 border-t border-white/10 md:border-0 pt-2 md:pt-0"
        >
            @php
                $navItems = [
                    ['route' => 'admin.dashboard', 'label' => 'แดชบอร์ด', 'icon' => 'layout-dashboard'],
                    ['route' => 'admin.clinics.index', 'label' => 'คลินิก', 'icon' => 'building-2'],
                    ['route' => 'admin.holidays.index', 'label' => 'วันหยุด', 'icon' => 'calendar-off'],
                    ['route' => 'admin.services.index', 'label' => 'บริการ', 'icon' => 'clipboard-list'],
                    ['route' => 'admin.therapists.index', 'label' => 'นักบำบัด', 'icon' => 'user-round'],
                    ['route' => 'admin.staff-leaves.index', 'label' => 'ลานักบำบัด', 'icon' => 'calendar-clock'],
                    ['route' => 'admin.promotions.index', 'label' => 'โปรโมชั่น', 'icon' => 'tag'],
                    ['route' => 'admin.booking-notifications.index', 'label' => 'แจ้งเตือน', 'icon' => 'bell', 'badge' => $unreadBookingNotificationsCount ?? 0],
                    ['route' => 'admin.appointments.index', 'label' => 'การจอง', 'icon' => 'calendar-days', 'badge' => $awaitingVerificationCount ?? 0],
                ];
            @endphp
            @foreach ($navItems as $item)
                @php
                    $activePattern = $item['route'] === 'admin.dashboard'
                        ? 'admin.dashboard'
                        : str_replace('.index', '.*', $item['route']);
                    $active = request()->routeIs($activePattern);
                @endphp
                <a
                    href="{{ route($item['route']) }}"
                    class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm transition {{ $active ? 'bg-emerald-600/90 text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"
                >
                    <i data-lucide="{{ $item['icon'] }}" class="size-4 shrink-0"></i>
                    {{ $item['label'] }}
                    @if (!empty($item['badge']))
                        <span class="ml-1 inline-flex min-w-[1.25rem] justify-center rounded-full bg-amber-500 px-1.5 py-0.5 text-xs font-bold text-white">{{ $item['badge'] }}</span>
                    @endif
                </a>
            @endforeach
            <a
                href="{{ config('app.frontend_url', 'http://localhost:3000') }}"
                target="_blank"
                rel="noopener"
                class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-300 hover:bg-white/10 hover:text-white transition"
            >
                <i data-lucide="external-link" class="size-4 shrink-0"></i>
                หน้าลูกค้า
            </a>
            <form method="POST" action="{{ route('admin.logout') }}" class="sm:hidden mt-1 pt-2 border-t border-white/10 w-full">
                @csrf
                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white transition">
                    <i data-lucide="log-out" class="size-4"></i>
                    ออกจากระบบ
                </button>
            </form>
        </div>
    </div>
</nav>
@endauth

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
    @if (session('success'))
        <div class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm sm:text-base">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-xl bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm sm:text-base">
            {{ session('error') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="mb-4 rounded-xl bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.lucide) lucide.createIcons();

        const toggle = document.getElementById('admin-nav-toggle');
        const menu = document.getElementById('admin-nav-menu');
        const iconOpen = document.getElementById('admin-nav-icon-open');
        const iconClose = document.getElementById('admin-nav-icon-close');

        if (toggle && menu) {
            toggle.addEventListener('click', function () {
                const open = menu.classList.toggle('admin-nav-open');
                menu.classList.toggle('hidden', !open);
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                iconOpen?.classList.toggle('hidden', open);
                iconClose?.classList.toggle('hidden', !open);
                if (window.lucide) lucide.createIcons();
            });

            menu.querySelectorAll('a').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (window.innerWidth < 768) {
                        menu.classList.remove('admin-nav-open');
                        menu.classList.add('hidden');
                        toggle.setAttribute('aria-expanded', 'false');
                        iconOpen?.classList.remove('hidden');
                        iconClose?.classList.add('hidden');
                    }
                });
            });
        }
    });
</script>
@stack('scripts')
</body>
</html>
