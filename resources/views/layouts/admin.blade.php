<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') — Tiga Serangkai</title>
    @include('partials.theme-init')
    @include('partials.favicon')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-surface text-ink">
    <aside class="panel-sidebar panel-sidebar-admin border-r border-brand/10 bg-[#0B1F2A] text-white">
        <div class="border-b border-white/10 px-5 py-5">
            <x-brand-logo class="h-14 w-auto brightness-0 invert" />
            <p class="mt-2 text-xs text-white/60">Admin Panel</p>
        </div>
        <nav class="flex flex-col gap-1 p-3">
            <a href="{{ route('admin.dashboard') }}" class="rounded-lg px-3 py-2.5 text-sm hover:bg-white/10 {{ request()->routeIs('admin.dashboard') ? 'bg-white/10' : '' }}">Dashboard</a>
            <a href="{{ route('admin.users.index') }}" class="rounded-lg px-3 py-2.5 text-sm hover:bg-white/10 {{ request()->routeIs('admin.users.*') ? 'bg-white/10' : '' }}">Users</a>
            <a href="{{ route('admin.programs.index', ['type' => 'internship']) }}" class="rounded-lg px-3 py-2.5 text-sm hover:bg-white/10 {{ request()->routeIs('admin.programs.*') && request('type', 'internship') === 'internship' && ! in_array(request('type'), ['job', 'bootcamp'], true) ? 'bg-white/10' : '' }}">Lowongan Magang</a>
            <a href="{{ route('admin.programs.index', ['type' => 'job']) }}" class="rounded-lg px-3 py-2.5 text-sm hover:bg-white/10 {{ request()->routeIs('admin.programs.*') && request('type') === 'job' ? 'bg-white/10' : '' }}">Lowongan Kerja</a>
            <a href="{{ route('admin.programs.index', ['type' => 'bootcamp']) }}" class="rounded-lg px-3 py-2.5 text-sm hover:bg-white/10 {{ request()->routeIs('admin.programs.*') && request('type') === 'bootcamp' ? 'bg-white/10' : '' }}">Bootcamp</a>
            <a href="{{ route('admin.applications.index') }}" class="rounded-lg px-3 py-2.5 text-sm hover:bg-white/10 {{ request()->routeIs('admin.applications.*') ? 'bg-white/10' : '' }}">Seleksi Magang</a>
            <a href="{{ route('admin.job-applications.index') }}" class="rounded-lg px-3 py-2.5 text-sm hover:bg-white/10 {{ request()->routeIs('admin.job-applications.*') ? 'bg-white/10' : '' }}">Seleksi Lowongan</a>
            <a href="{{ route('admin.grades.index') }}" class="rounded-lg px-3 py-2.5 text-sm hover:bg-white/10 {{ request()->routeIs('admin.grades.*') ? 'bg-white/10' : '' }}">Nilai Magang</a>
            <a href="{{ route('admin.schedules.index') }}" class="rounded-lg px-3 py-2.5 text-sm hover:bg-white/10 {{ request()->routeIs('admin.schedules.*') ? 'bg-white/10' : '' }}">Sesi Magang</a>
            <a href="{{ route('admin.chat.index') }}" class="rounded-lg px-3 py-2.5 text-sm hover:bg-white/10 {{ request()->routeIs('admin.chat.*') ? 'bg-white/10' : '' }}">Chat Magang</a>
            <a href="{{ route('admin.payments.index') }}" class="rounded-lg px-3 py-2.5 text-sm hover:bg-white/10 {{ request()->routeIs('admin.payments.*') ? 'bg-white/10' : '' }}">Pembayaran</a>
            <a href="{{ route('admin.cv-subscriptions.index') }}" class="rounded-lg px-3 py-2.5 text-sm hover:bg-white/10 {{ request()->routeIs('admin.cv-subscriptions.*') ? 'bg-white/10' : '' }}">Paket CV AI</a>
            <a href="{{ route('admin.content.index') }}" class="rounded-lg px-3 py-2.5 text-sm hover:bg-white/10 {{ request()->routeIs('admin.content.*') ? 'bg-white/10' : '' }}">Berita & Content</a>
            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button class="w-full rounded-lg px-3 py-2.5 text-left text-sm hover:bg-white/10" type="submit">Logout</button>
            </form>
        </nav>
    </aside>

    <div class="panel-main panel-main-admin">
        <header class="flex items-center justify-between border-b border-brand/10 bg-panel/80 px-6 py-4 backdrop-blur">
            <h1 class="font-display text-xl font-semibold text-ink">@yield('heading', 'Admin')</h1>
            <div class="flex items-center gap-3">
                @include('partials.theme-toggle')
                @include('partials.notification-bell')
                <span class="text-sm text-ink-soft">{{ auth()->user()->name }}</span>
            </div>
        </header>

        @if (session('success'))
            <div class="mx-6 mt-4 rounded-xl border border-brand/30 bg-brand/10 px-4 py-3 text-sm text-brand-deeper">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mx-6 mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                {{ session('error') }}
            </div>
        @endif

        <div class="p-6">
            @yield('content')
        </div>
    </div>
</body>
</html>
