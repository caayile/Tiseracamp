<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Mentor') — Tiga Serangkai</title>
    @include('partials.theme-init')
    @include('partials.favicon')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-surface text-ink">
    @php
        $mentor = auth()->user();
        $initials = collect(explode(' ', $mentor->name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
    @endphp

    <aside class="panel-sidebar panel-sidebar-mentor">
        <div class="panel-sidebar-header border-b px-5 py-5">
            <x-brand-logo class="panel-sidebar-logo h-14 w-auto" />
            <p class="panel-sidebar-subtitle">Mentor Panel</p>
        </div>

        <nav class="flex flex-1 flex-col gap-1 overflow-y-auto p-3">
            @php
                $navGroups = [
                    'Overview' => [
                        ['label' => 'Dashboard', 'route' => 'mentor.dashboard', 'match' => 'mentor.dashboard', 'icon' => 'M4 4h7v7H4V4zm9 0h7v7h-7V4zM4 13h7v7H4v-7zm9 0h7v7h-7v-7z'],
                    ],
                    'Program' => [
                        ['label' => 'Bootcamp Saya', 'route' => 'mentor.programs.index', 'match' => 'mentor.programs.*', 'icon' => 'M12 14l9-5-9-5-9 5 9 5zM12 14l6.16-3.422A12.083 12.083 0 0112 21.5 12.083 12.083 0 015.84 10.578L12 14zM12 14v5'],
                        ['label' => 'Tambah Bootcamp', 'route' => 'mentor.programs.create', 'match' => 'mentor.programs.create', 'icon' => 'M12 4v16m8-8H4'],
                    ],
                    'Mentoring' => [
                        ['label' => 'Logbook Peserta', 'route' => 'mentor.logbooks.index', 'match' => 'mentor.logbooks.*', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                        ['label' => 'Review Tugas', 'route' => 'mentor.submissions', 'match' => 'mentor.submissions*', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['label' => 'Jadwal Mentoring', 'route' => 'mentor.schedules.index', 'match' => 'mentor.schedules.*', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                        ['label' => 'Pengumuman', 'route' => 'mentor.announcements.index', 'match' => 'mentor.announcements.*', 'icon' => 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z'],
                        ['label' => 'Diskusi', 'route' => 'mentor.discussions.index', 'match' => 'mentor.discussions.*', 'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
                        ['label' => 'Chat Siswa', 'route' => 'mentor.chat.index', 'match' => 'mentor.chat.*', 'icon' => 'M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z'],
                    ],
                    'Magang' => [
                        ['label' => 'Pendaftar Magang', 'route' => 'mentor.applications.index', 'match' => 'mentor.applications.*', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['label' => 'Nilai Magang', 'route' => 'mentor.grades.index', 'match' => 'mentor.grades.*', 'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'],
                    ],
                    'Akun' => [
                        ['label' => 'Edit Profil', 'route' => 'profile.edit', 'match' => 'profile.edit', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                        ['label' => 'Lihat Situs', 'route' => 'home', 'match' => 'home', 'icon' => 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ],
                ];
            @endphp

            @foreach ($navGroups as $groupLabel => $items)
                <p class="panel-sidebar-label mt-3 px-3 text-[10px] font-bold uppercase tracking-[0.14em] first:mt-0">{{ $groupLabel }}</p>
                @foreach ($items as $item)
                    @php
                        $isActive = request()->routeIs($item['match']);
                        if (($item['route'] ?? '') === 'mentor.programs.index') {
                            $isActive = request()->routeIs('mentor.programs.*') && ! request()->routeIs('mentor.programs.create');
                        }
                    @endphp
                    <a href="{{ route($item['route']) }}" class="panel-sidebar-link flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition {{ $isActive ? 'is-active' : '' }}">
                        <svg class="panel-sidebar-icon h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                        </svg>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            @endforeach
        </nav>

        <div class="panel-sidebar-footer border-t p-3">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="panel-sidebar-link flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm transition" type="submit">
                    <svg class="panel-sidebar-icon h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <div class="panel-main panel-main-mentor">
        <header class="sticky top-0 z-30 border-b border-brand/10 bg-panel/80 backdrop-blur-xl">
            <div class="flex items-center justify-between gap-4 px-4 py-3 sm:px-6">
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-brand-dark">Mentor Panel</p>
                    <h1 class="truncate font-display text-xl font-semibold text-ink sm:text-2xl">@yield('heading', 'Mentor')</h1>
                </div>

                <div class="flex items-center gap-2 sm:gap-4">
                    @include('partials.theme-toggle')
                    @include('partials.notification-bell')

                    <a href="{{ route('mentor.programs.create') }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-[#0B1F2A] px-4 py-2.5 text-sm font-semibold text-white shadow-md transition hover:-translate-y-0.5 hover:bg-[#065A7A] sm:px-5">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#27CCF5] text-base font-bold leading-none text-[#0B1F2A]">+</span>
                        <span class="hidden sm:inline">Tambah Bootcamp</span>
                    </a>

                    <div class="relative z-50" data-profile-menu>
                        <button type="button" data-profile-toggle
                                class="flex items-center gap-3 rounded-2xl border border-[#0B1F2A]/10 bg-white py-1.5 pl-1.5 pr-3 shadow-sm transition hover:border-[#27CCF5]/50 hover:shadow-md"
                                aria-expanded="false"
                                aria-haspopup="menu"
                                aria-label="Menu profil">
                            @if ($mentor->avatar)
                                <img src="{{ media_url($mentor->avatar) }}" alt="{{ $mentor->name }}" class="h-11 w-11 rounded-xl object-cover ring-2 ring-[#27CCF5]/40">
                            @else
                                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#0B1F2A] font-display text-sm font-bold text-[#27CCF5]">
                                    {{ strtoupper($initials) }}
                                </span>
                            @endif
                            <span class="hidden text-left sm:block">
                                <span class="block text-sm font-semibold leading-tight text-[#0B1F2A]">{{ $mentor->name }}</span>
                                <span class="block text-[11px] text-[#0B9BC4]">Mentor · {{ number_format($mentor->rating, 1) }}★</span>
                            </span>
                            <svg class="hidden h-4 w-4 text-slate-400 sm:block" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"/></svg>
                        </button>

                        <div class="absolute right-0 z-50 mt-2 hidden w-72 overflow-hidden rounded-2xl border border-brand/15 bg-panel shadow-xl" data-profile-panel role="menu">
                            <div class="bg-gradient-to-br from-brand/20 via-panel to-brand-mist p-5">
                                <div class="flex items-center gap-3">
                                    @if ($mentor->avatar)
                                        <img src="{{ media_url($mentor->avatar) }}" alt="" class="h-14 w-14 rounded-xl object-cover">
                                    @else
                                        <span class="flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-brand to-brand-deeper font-display text-lg font-bold text-white">{{ strtoupper($initials) }}</span>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="truncate text-base font-semibold text-ink">{{ $mentor->name }}</p>
                                        <p class="truncate text-sm text-ink-soft">{{ $mentor->email }}</p>
                                    </div>
                                </div>
                                @if ($mentor->expertise)
                                    <div class="mt-3 flex flex-wrap gap-1">
                                        @foreach (array_slice($mentor->expertise ?? [], 0, 4) as $skill)
                                            <span class="rounded-lg bg-panel/80 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-brand-deeper">{{ $skill }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <div class="p-2">
                                <a href="{{ route('profile.edit') }}" class="block rounded-xl px-3 py-2.5 text-sm font-medium text-ink transition hover:bg-brand/10" role="menuitem">Edit profil</a>
                                <a href="{{ route('mentor.programs.index') }}" class="block rounded-xl px-3 py-2.5 text-sm font-medium text-ink transition hover:bg-brand/10" role="menuitem">Program saya</a>
                                <form method="POST" action="{{ route('logout') }}" class="border-t border-ink/8 mt-1 pt-1">
                                    @csrf
                                    <button class="w-full rounded-xl px-3 py-2.5 text-left text-sm font-medium text-red-600 transition hover:bg-red-50" type="submit" role="menuitem">Keluar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex gap-2 overflow-x-auto border-t border-brand/10 px-4 py-2 md:hidden">
                <a href="{{ route('mentor.dashboard') }}" class="btn-ghost whitespace-nowrap text-xs">Dashboard</a>
                <a href="{{ route('mentor.programs.index') }}" class="btn-ghost whitespace-nowrap text-xs">Program</a>
                <a href="{{ route('mentor.schedules.index') }}" class="btn-ghost whitespace-nowrap text-xs">Jadwal</a>
                <a href="{{ route('mentor.submissions') }}" class="btn-ghost whitespace-nowrap text-xs">Tugas</a>
                <a href="{{ route('mentor.announcements.index') }}" class="btn-ghost whitespace-nowrap text-xs">Pengumuman</a>
                <a href="{{ route('mentor.chat.index') }}" class="btn-ghost whitespace-nowrap text-xs">Chat</a>
            </div>
        </header>

        @if (session('success'))
            <div class="mx-4 mt-4 rounded-xl border border-brand/30 bg-brand/10 px-4 py-3 text-sm text-brand-deeper sm:mx-6">
                {{ session('success') }}
            </div>
        @endif

        <div class="p-4 sm:p-6">
            @yield('content')
        </div>
    </div>

    <script>
        (function () {
            const sidebar = document.querySelector('.panel-sidebar-mentor');
            if (! sidebar) return;

            const scroller = sidebar.querySelector('nav') || sidebar;
            const key = 'mentor-sidebar-scroll';
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
