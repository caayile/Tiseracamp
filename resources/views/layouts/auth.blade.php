<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — Tiga Serangkai</title>
    @include('partials.theme-init')
    @include('partials.favicon')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-surface text-ink antialiased">
    <div class="relative min-h-screen">
        <img
            src="{{ asset('images/auth-building.png') }}"
            alt=""
            class="fixed inset-0 h-full w-full object-cover"
            aria-hidden="true"
        >
        <div class="fixed inset-0 bg-ink/45 backdrop-blur-[1px] dark:bg-black/55"></div>

        <div class="absolute right-4 top-4 z-20 sm:right-6 sm:top-6">
            @include('partials.theme-toggle')
        </div>

        <main class="relative flex min-h-screen items-center justify-center px-4 py-8 sm:px-8 lg:py-12">
            <div class="auth-card w-full max-w-md rounded-[2rem] border border-white/35 bg-brand-mist/35 p-6 shadow-[0_30px_80px_-20px_rgba(11,31,42,0.65)] backdrop-blur-2xl sm:p-8">
                @if (session('success'))
                    <div class="mb-4 rounded-xl border border-brand/30 bg-brand/10 px-4 py-3 text-sm text-brand-mid">
                        {{ session('success') }}
                    </div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
