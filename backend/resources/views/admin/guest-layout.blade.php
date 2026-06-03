<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>@yield('title', 'แอดมิน') — ระบบจองคลินิก</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-900 min-h-screen">
<main class="max-w-6xl mx-auto px-4 py-8">
    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-100 border border-red-300 text-red-800 px-4 py-3 max-w-md mx-auto">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @yield('content')
</main>
</body>
</html>
