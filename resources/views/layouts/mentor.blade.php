<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Mentor') — Tiga Serangkai</title>
    @include('partials.favicon')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#F7FBFD]">
    @php
        $mentor = auth()->user();
        $initials = collect(explode(' ', $mentor->name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
    @endphp

    <div class="min-h-screen lg:grid lg:grid-cols-[260px_1fr]">
        {{-- Sidebar --}}
        <aside class="relative hidden overflow-hidden bg-gradient-to-b from-[#0B1F2A] via-[#0A3A4A] to-[#065A7A] text-white lg:flex lg:flex-col">
            <div class="pointer-events-none absolute -right-16 top-24 h-48 w-48 rounded-full bg-brand/20 blur-3xl"></div>
            <div class="border-b border-white/10 px-6 py-6">
                <div class="flex items-center gap-3">
                    <x-brand-logo class="h-12 w-auto brightness-0 invert" />
                </div>
            </div>

            <nav class="flex flex-1 flex-col gap-1 p-4">
                @php
                    $links = [
                        ['route' => 'mentor.dashboard', 'label' => 'Dashboard', 'match' => 'mentor.dashboard'],
                        ['route' => 'mentor.programs.index', 'label' => 'Program Saya', 'match' => 'mentor.programs.*'],
                        ['route' => 'mentor.applications.index', 'label' => 'Seleksi Magang', 'match' => 'mentor.applications.*'],
                        ['route' => 'mentor.submissions', 'label' => 'Review Tugas', 'match' => 'mentor.submissions*'],
                        ['route' => 'mentor.schedules.index', 'label' => 'Jadwal Mentoring', 'match' => 'mentor.schedules.*'],
                        ['route' => 'mentor.chat.index', 'label' => 'Chat Siswa', 'match' => 'mentor.chat.*'],
                        ['route' => 'profile.edit', 'label' => 'Edit Profil', 'match' => 'profile.*'],
                    ];
                @endphp
                @foreach ($links as $link)
                    <a href="{{ route($link['route']) }}"
                       class="rounded-xl px-4 py-2.5 text-sm font-medium transition {{ request()->routeIs($link['match']) ? 'bg-white/15 text-white shadow-inner' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                        {{ $link['label'] }}
                    </a>
                @endforeach

                <a href="{{ route('home') }}" class="mt-auto rounded-xl px-4 py-2.5 text-sm text-white/60 transition hover:bg-white/10 hover:text-white">Lihat situs publik</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="w-full rounded-xl px-4 py-2.5 text-left text-sm text-white/60 transition hover:bg-white/10 hover:text-white" type="submit">Keluar</button>
                </form>
            </nav>
        </aside>

        {{-- Main --}}
        <div class="flex min-h-screen flex-col">
            <header class="sticky top-0 z-30 border-b border-brand/10 bg-white/80 backdrop-blur-xl">
                <div class="flex items-center justify-between gap-4 px-4 py-3 sm:px-6">
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-brand-dark">Mentor Panel</p>
                        <h1 class="truncate font-display text-xl font-semibold text-ink sm:text-2xl">@yield('heading', 'Mentor')</h1>
                    </div>

                    <div class="flex items-center gap-2 sm:gap-4">
                        @include('partials.notification-bell')

                        <a href="{{ route('mentor.programs.create') }}"
                           class="inline-flex items-center gap-2 rounded-xl bg-[#0B1F2A] px-4 py-2.5 text-sm font-semibold text-white shadow-md transition hover:-translate-y-0.5 hover:bg-[#065A7A] sm:px-5">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#27CCF5] text-base font-bold leading-none text-[#0B1F2A]">+</span>
                            <span class="hidden sm:inline">Tambah Program</span>
                        </a>

                        {{-- Mentor profile (top right) --}}
                        <div class="relative" data-profile-menu>
                            <button type="button" data-profile-toggle
                                    class="flex items-center gap-3 rounded-2xl border border-[#0B1F2A]/10 bg-white py-1.5 pl-1.5 pr-3 shadow-sm transition hover:border-[#27CCF5]/50 hover:shadow-md"
                                    aria-expanded="false">
                                @if ($mentor->avatar)
                                    <img src="{{ asset('storage/'.$mentor->avatar) }}" alt="{{ $mentor->name }}" class="h-11 w-11 rounded-xl object-cover ring-2 ring-[#27CCF5]/40">
                                @else
                                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#0B1F2A] font-display text-sm font-bold text-[#27CCF5]">
                                        {{ strtoupper($initials) }}
                                    </span>
                                @endif
                                <span class="hidden text-left sm:block">
                                    <span class="block text-sm font-semibold leading-tight text-[#0B1F2A]">{{ $mentor->name }}</span>
                                    <span class="block text-[11px] text-[#0B9BC4]">Mentor · {{ number_format($mentor->rating, 1) }}★</span>
                                </span>
                                <svg class="hidden h-4 w-4 text-slate-400 sm:block" viewBox="0 0 20 20" fill="currentColor"><path d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"/></svg>
                            </button>

                            <div class="absolute right-0 mt-2 hidden w-72 overflow-hidden rounded-2xl border border-brand/15 bg-white shadow-xl" data-profile-panel>
                                <div class="bg-gradient-to-br from-brand/20 via-white to-brand-mist p-5">
                                    <div class="flex items-center gap-3">
                                        @if ($mentor->avatar)
                                            <img src="{{ asset('storage/'.$mentor->avatar) }}" alt="" class="h-14 w-14 rounded-xl object-cover">
                                        @else
                                            <span class="flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-brand to-brand-deeper font-display text-lg font-bold text-white">{{ strtoupper($initials) }}</span>
                                        @endif
                                        <div>
                                            <p class="text-base font-semibold text-ink">{{ $mentor->name }}</p>
                                            <p class="text-sm text-ink-soft">{{ $mentor->email }}</p>
                                        </div>
                                    </div>
                                    @if ($mentor->expertise)
                                        <div class="mt-3 flex flex-wrap gap-1">
                                            @foreach (array_slice($mentor->expertise ?? [], 0, 4) as $skill)
                                                <span class="rounded-lg bg-white/80 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-brand-deeper">{{ $skill }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <div class="p-2">
                                    <a href="{{ route('profile.edit') }}" class="block rounded-xl px-3 py-2 text-sm hover:bg-brand/10">Kelola profil mentor</a>
                                    <a href="{{ route('mentor.programs.index') }}" class="block rounded-xl px-3 py-2 text-sm hover:bg-brand/10">Program saya</a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button class="w-full rounded-xl px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50" type="submit">Keluar</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Mobile nav --}}
                <div class="flex gap-2 overflow-x-auto border-t border-brand/10 px-4 py-2 lg:hidden">
                    <a href="{{ route('mentor.dashboard') }}" class="btn-ghost whitespace-nowrap text-xs">Dashboard</a>
                    <a href="{{ route('mentor.programs.index') }}" class="btn-ghost whitespace-nowrap text-xs">Program</a>
                    <a href="{{ route('mentor.chat.index') }}" class="btn-ghost whitespace-nowrap text-xs">Chat</a>
                    <a href="{{ route('mentor.submissions') }}" class="btn-ghost whitespace-nowrap text-xs">Tugas</a>
                </div>
            </header>

            @if (session('success'))
                <div class="mx-4 mt-4 rounded-xl border border-brand/30 bg-brand/10 px-4 py-3 text-sm text-brand-deeper sm:mx-6">
                    {{ session('success') }}
                </div>
            @endif

            <div class="flex-1 p-4 sm:p-6">
                @yield('content')
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const root = document.querySelector('[data-profile-menu]');
            if (!root) return;
            const toggle = root.querySelector('[data-profile-toggle]');
            const panel = root.querySelector('[data-profile-panel]');
            toggle?.addEventListener('click', (e) => {
                e.stopPropagation();
                panel.classList.toggle('hidden');
                toggle.setAttribute('aria-expanded', panel.classList.contains('hidden') ? 'false' : 'true');
            });
            document.addEventListener('click', () => panel?.classList.add('hidden'));
        });
    </script>
</body>
</html>
