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
        <nav class="flex flex-1 flex-col gap-1 overflow-y-auto p-3">
            @php
                $navGroups = [
                    'Overview' => [
                        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'icon' => 'M4 4h7v7H4V4zm9 0h7v7h-7V4zM4 13h7v7H4v-7zm9 0h7v7h-7v-7z'],
                        ['label' => 'Users', 'route' => 'admin.users.index', 'match' => 'admin.users.*', 'icon' => 'M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zm13 10v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75'],
                    ],
                    'Lowongan' => [
                        ['label' => 'Lowongan Magang', 'route' => 'admin.programs.index', 'params' => ['type' => 'internship'], 'match' => 'admin.programs.*', 'icon' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'active' => request('type', 'internship') === 'internship' && ! in_array(request('type'), ['job', 'bootcamp'], true)],
                        ['label' => 'Lowongan Kerja', 'route' => 'admin.programs.index', 'params' => ['type' => 'job'], 'match' => 'admin.programs.*', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'active' => request('type') === 'job'],
                        ['label' => 'Bootcamp', 'route' => 'admin.programs.index', 'params' => ['type' => 'bootcamp'], 'match' => 'admin.programs.*', 'icon' => 'M12 14l9-5-9-5-9 5 9 5zM12 14l6.16-3.422A12.083 12.083 0 0112 21.5 12.083 12.083 0 015.84 10.578L12 14zM12 14v5', 'active' => request('type') === 'bootcamp'],
                    ],
                    'Seleksi' => [
                        ['label' => 'Seleksi Magang', 'route' => 'admin.applications.index', 'match' => 'admin.applications.*', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['label' => 'Seleksi Lowongan', 'route' => 'admin.job-applications.index', 'match' => 'admin.job-applications.*', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
                    ],
                    'Manajemen Magang' => [
                        ['label' => 'Galeri Portofolio', 'route' => 'admin.portfolios.index', 'match' => 'admin.portfolios.*', 'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
                        ['label' => 'Nilai Magang', 'route' => 'admin.grades.index', 'match' => 'admin.grades.*', 'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'],
                        ['label' => 'Logbook Magang', 'route' => 'admin.logbooks.index', 'match' => 'admin.logbooks.*', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                        ['label' => 'Sesi Magang', 'route' => 'admin.schedules.index', 'match' => 'admin.schedules.*', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                    ],
                    'Keuangan' => [
                        ['label' => 'Pembayaran', 'route' => 'admin.payments.index', 'match' => 'admin.payments.*', 'icon' => 'M3 10h18M7 15h3m-6 4h16a2 2 0 002-2V7a2 2 0 00-2-2H3a2 2 0 00-2 2v10a2 2 0 002 2z'],
                        ['label' => 'Paket CV AI', 'route' => 'admin.cv-subscriptions.index', 'match' => 'admin.cv-subscriptions.*', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                        ['label' => 'Rekening Bayar', 'route' => 'admin.payment-account.edit', 'match' => 'admin.payment-account.*', 'icon' => 'M3 6h18v12H3V6zM3 10h18M7 15h2'],
                        ['label' => 'Harga Paket CV', 'route' => 'admin.cv-plans.index', 'match' => 'admin.cv-plans.*', 'icon' => 'M20 12V8a2 2 0 00-2-2H6a2 2 0 00-2 2v4m16 0a2 2 0 00-2 2h-2a2 2 0 00-2 2h-4a2 2 0 00-2-2H6a2 2 0 00-2 2m16 0v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4'],
                    ],
                    'Konten' => [
                        ['label' => 'Berita', 'route' => 'admin.content.index', 'match' => 'admin.content.*', 'icon' => 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v14m2-12v12a2 2 0 01-2 2M5 9h9'],
                        ['label' => 'Materi Karier', 'route' => 'admin.career-resources.index', 'match' => 'admin.career-resources.*', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                        ['label' => 'Badge', 'route' => 'admin.achievements.index', 'match' => 'admin.achievements.*', 'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'],
                        ['label' => 'Syarat & Privasi', 'route' => 'admin.site-pages.edit', 'match' => 'admin.site-pages.*', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                        ['label' => 'Mitra', 'route' => 'admin.partners.index', 'match' => 'admin.partners.*', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                        ['label' => 'Testimoni', 'route' => 'admin.testimonials.index', 'match' => 'admin.testimonials.*', 'icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z'],
                        ['label' => 'Chat Magang', 'route' => 'admin.chat.index', 'match' => 'admin.chat.*', 'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
                    ],
                    'Akun' => [
                        ['label' => 'Edit Profil', 'route' => 'profile.edit', 'match' => 'profile.edit', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                        ['label' => 'Lihat Situs', 'route' => 'home', 'match' => 'home', 'icon' => 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ],
                ];
            @endphp

            @foreach ($navGroups as $groupLabel => $items)
                <p class="mt-3 px-3 text-[10px] font-bold uppercase tracking-[0.14em] text-white/40 first:mt-0">{{ $groupLabel }}</p>
                @foreach ($items as $item)
                    @php
                        $isActive = request()->routeIs($item['match']) && ($item['active'] ?? true);
                        $itemRoute = isset($item['params'])
                            ? route($item['route'], $item['params'])
                            : route($item['route']);
                    @endphp
                    <a href="{{ $itemRoute }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition hover:bg-white/10 {{ $isActive ? 'bg-white/10' : '' }}">
                        <svg class="h-5 w-5 shrink-0 text-white/70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                        </svg>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            @endforeach
        </nav>

        <div class="border-t border-white/10 p-3">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm transition hover:bg-white/10" type="submit">
                    <svg class="h-5 w-5 shrink-0 text-white/70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </button>
            </form>
        </div>
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

    <script>
        (function () {
            const sidebar = document.querySelector('.panel-sidebar-admin');
            if (! sidebar) return;

            const scroller = sidebar.querySelector('nav') || sidebar;
            const key = 'admin-sidebar-scroll';
            const saved = sessionStorage.getItem(key);
            if (saved) {
                scroller.scrollTop = parseInt(saved, 10);
            }

            sidebar.querySelectorAll('nav a').forEach(function (link) {
                link.addEventListener('click', function () {
                    sessionStorage.setItem(key, String(scroller.scrollTop));
                });
            });
        })();
    </script>
</body>
</html>
