<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') — Tiga Serangkai</title>
    @include('partials.favicon')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-surface">
    <div class="min-h-screen md:grid md:grid-cols-[240px_1fr]">
        <aside class="border-r border-brand/10 bg-ink text-white">
            <div class="border-b border-white/10 px-5 py-5">
                <x-brand-logo class="h-14 w-auto brightness-0 invert" />
                <p class="mt-2 text-xs text-white/60">Admin Panel</p>
            </div>
            <nav class="flex flex-col gap-1 p-3">
                <a href="{{ route('admin.dashboard') }}" class="rounded-lg px-3 py-2 text-sm hover:bg-white/10">Dashboard</a>
                <a href="{{ route('admin.users.index') }}" class="rounded-lg px-3 py-2 text-sm hover:bg-white/10">Users</a>
                <a href="{{ route('admin.programs.index') }}" class="rounded-lg px-3 py-2 text-sm hover:bg-white/10">Programs</a>
                <a href="{{ route('admin.payments.index') }}" class="rounded-lg px-3 py-2 text-sm hover:bg-white/10">Payments</a>
                <a href="{{ route('admin.content.index') }}" class="rounded-lg px-3 py-2 text-sm hover:bg-white/10">Content</a>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button class="w-full rounded-lg px-3 py-2 text-left text-sm hover:bg-white/10" type="submit">Logout</button>
                </form>
            </nav>
        </aside>

        <div>
            <header class="flex items-center justify-between border-b border-brand/10 bg-white/80 px-6 py-4 backdrop-blur">
                <h1 class="font-display text-xl font-semibold">@yield('heading', 'Admin')</h1>
                <div class="flex items-center gap-3">
                    @include('partials.notification-bell')
                    <span class="text-sm text-ink-soft">{{ auth()->user()->name }}</span>
                </div>
            </header>

            @if (session('success'))
                <div class="mx-6 mt-4 rounded-xl border border-brand/30 bg-brand/10 px-4 py-3 text-sm text-brand-deeper">
                    {{ session('success') }}
                </div>
            @endif

            <div class="p-6">
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
