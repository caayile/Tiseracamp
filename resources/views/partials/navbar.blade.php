<header class="sticky top-0 z-40 border-b border-ink/8 bg-white/90 backdrop-blur-xl">
    <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3">
        <a href="{{ route('home') }}" class="group flex items-center gap-3">
            <x-brand-logo class="h-11 w-auto transition group-hover:scale-105" />
        </a>

        <nav class="hidden items-center gap-1 md:flex">
            <a href="{{ route('home') }}" class="btn-ghost">Beranda</a>
            <a href="{{ route('programs.index') }}" class="btn-ghost {{ request()->routeIs('programs.*') ? 'text-ink font-semibold' : '' }}">Bootcamp & Program</a>
            <a href="{{ route('programs.index', ['type' => 'internship']) }}" class="btn-ghost">Magang</a>
            @auth
                @if (auth()->user()->isStudent())
                    <a href="{{ route('career.index') }}" class="btn-ghost">Karier</a>
                @endif
            @endauth
        </nav>

        <div class="hidden items-center gap-2 md:flex">
            @auth
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="btn-ghost">Admin</a>
                @endif
                @if (auth()->user()->isMentor())
                    <a href="{{ route('mentor.dashboard') }}" class="btn-navy">Panel Mentor</a>
                @endif
                @if (auth()->user()->isStudent())
                    <a href="{{ route('chat.index') }}" class="btn-ghost">Chat</a>
                @endif
                @include('partials.notification-bell')
                <a href="{{ route('dashboard') }}" class="btn-secondary">Dashboard</a>
                @include('partials.profile-menu')
            @else
                <a href="{{ route('login') }}" class="inline-flex items-center rounded-xl border border-brand px-5 py-2 text-sm font-semibold text-brand-mid transition hover:bg-brand-mist">Masuk</a>
                <a href="{{ route('register') }}" class="btn-primary pulse-soft">Daftar</a>
            @endauth
        </div>

        <button type="button" class="btn-secondary md:hidden" data-nav-toggle aria-expanded="false" aria-label="Menu">
            Menu
        </button>
    </div>

    <div class="hidden border-t border-ink/8 bg-white px-4 py-4 md:hidden" data-nav-panel>
        <div class="mx-auto flex max-w-6xl flex-col gap-2">
            <a href="{{ route('home') }}" class="btn-ghost justify-start">Beranda</a>
            <a href="{{ route('programs.index') }}" class="btn-ghost justify-start">Program</a>
            <a href="{{ route('programs.index', ['type' => 'bootcamp']) }}" class="btn-ghost justify-start">Bootcamp</a>
            <a href="{{ route('programs.index', ['type' => 'internship']) }}" class="btn-ghost justify-start">Magang</a>
            @auth
                <div class="flex items-center gap-2 px-1 py-1">
                    @include('partials.notification-bell')
                    @include('partials.profile-menu')
                    <a href="{{ route('dashboard') }}" class="btn-primary flex-1 justify-center">Dashboard</a>
                </div>
            @else
                <a href="{{ route('login') }}" class="btn-secondary">Masuk</a>
                <a href="{{ route('register') }}" class="btn-primary">Daftar</a>
            @endauth
        </div>
    </div>
</header>
