@php
    $type = request('type');
    $isHome = request()->routeIs('home');
    $isCvReview = request()->routeIs('cv-review.*');
    $isMagang = (request()->routeIs('programs.index') && $type === 'internship')
        || request()->routeIs('internships.*');
    $isPrograms = request()->routeIs('programs.*') && ! $isMagang;
    $isNews = request()->routeIs('news.*');
    $isCareer = request()->routeIs('career.*');

    // Hindari query Program ekstra di navbar — type di-share dari controller.
    if (request()->routeIs('programs.show')) {
        $programType = view()->shared('navProgramType');
        if ($programType === 'internship') {
            $isMagang = true;
            $isPrograms = false;
        } elseif (filled($programType)) {
            $isMagang = false;
            $isPrograms = true;
        }
    }

    $navClass = 'inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-medium text-ink-soft transition hover:bg-brand/10 hover:text-ink';
    $navActive = 'inline-flex items-center justify-center rounded-xl bg-brand/20 px-4 py-2 text-sm font-semibold text-ink shadow-[0_6px_16px_-8px_rgba(11,31,42,0.45)] ring-1 ring-brand/30 -translate-y-0.5';
@endphp

<header class="sticky top-0 z-40 border-b border-ink/8 bg-panel/90 backdrop-blur-xl">
    <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3">
        <a href="{{ route('home') }}" class="group flex items-center gap-3">
            <x-brand-logo class="h-11 w-auto transition group-hover:scale-105" />
        </a>

        <nav class="hidden items-center gap-1 md:flex">
            <a href="{{ route('home') }}" class="{{ $isHome ? $navActive : $navClass }}">Beranda</a>
            <a href="{{ route('cv-review.index') }}" class="{{ $isCvReview ? $navActive : $navClass }}">Review CV AI</a>
            <a href="{{ route('programs.index') }}" class="{{ $isPrograms ? $navActive : $navClass }}">Bootcamp & Program</a>
            <a href="{{ route('programs.index', ['type' => 'internship']) }}" class="{{ $isMagang ? $navActive : $navClass }}">Magang</a>
            <a href="{{ route('news.index') }}" class="{{ $isNews ? $navActive : $navClass }}">Berita</a>
            @auth
                @if (auth()->user()->isStudent())
                    <div class="group relative">
                        <a href="{{ route('career.gallery') }}" class="{{ $isCareer ? $navActive : $navClass }} inline-flex items-center gap-2" aria-haspopup="menu" aria-controls="career-menu-desktop" data-career-toggle="desktop">
                            Karier
                            <svg class="h-4 w-4 text-ink-soft transition duration-200 group-hover:rotate-180 group-focus-within:rotate-180" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path d="M6 8l4 4 4-4" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </a>

                        <div id="career-menu-desktop" class="invisible absolute left-0 top-full z-50 mt-2 w-56 origin-top scale-95 overflow-hidden rounded-2xl border border-ink/10 bg-panel p-2 opacity-0 shadow-[0_24px_70px_-32px_rgba(11,31,42,0.35)] transition duration-200 group-hover:visible group-hover:scale-100 group-hover:opacity-100 group-focus-within:visible group-focus-within:scale-100 group-focus-within:opacity-100" data-career-menu="desktop">
                            <a href="{{ route('career.gallery') }}" class="block rounded-xl px-4 py-2.5 text-sm font-medium text-ink transition hover:bg-brand-mist">
                                Galeri Portofolio
                            </a>
                            <a href="{{ route('career.jobs') }}" class="mt-0.5 block rounded-xl px-4 py-2.5 text-sm font-medium text-ink transition hover:bg-brand-mist">
                                Lowongan Kerja
                            </a>
                        </div>
                    </div>
                @endif
            @endauth
        </nav>

        <div class="hidden items-center gap-2 md:flex">
            @include('partials.theme-toggle')
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

        <div class="flex items-center gap-2 md:hidden">
            @include('partials.theme-toggle')
            <button type="button" class="btn-secondary" data-nav-toggle aria-expanded="false" aria-label="Menu">
                Menu
            </button>
        </div>
    </div>

    <div class="hidden border-t border-ink/8 bg-panel px-4 py-4 md:hidden" data-nav-panel>
        <div class="mx-auto flex max-w-6xl flex-col gap-2">
            <a href="{{ route('home') }}" class="{{ ($isHome ? $navActive : $navClass).' justify-start' }}">Beranda</a>
            <a href="{{ route('cv-review.index') }}" class="{{ ($isCvReview ? $navActive : $navClass).' justify-start' }}">Review CV AI</a>
            <a href="{{ route('programs.index') }}" class="{{ ($isPrograms ? $navActive : $navClass).' justify-start' }}">Bootcamp & Program</a>
            <a href="{{ route('programs.index', ['type' => 'internship']) }}" class="{{ ($isMagang ? $navActive : $navClass).' justify-start' }}">Magang</a>
            <a href="{{ route('news.index') }}" class="{{ ($isNews ? $navActive : $navClass).' justify-start' }}">Berita</a>
            @auth
                @if (auth()->user()->isStudent())
                    <div>
                        <button type="button" class="inline-flex w-full items-center justify-between rounded-xl px-4 py-2 text-sm font-medium text-ink hover:bg-brand/10" data-career-toggle="mobile" aria-controls="career-menu-mobile" aria-expanded="false">
                            <span>Karier</span>
                            <svg class="h-4 w-4 text-ink-soft" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path d="M6 8l4 4 4-4" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                        <div id="career-menu-mobile" class="mt-2 hidden flex-col gap-1 rounded-2xl border border-ink/10 bg-panel p-2" data-career-menu="mobile">
                            <a href="{{ route('career.gallery') }}" class="inline-flex w-full items-center rounded-xl px-4 py-2 text-sm font-medium text-ink transition hover:bg-brand-mist">Galeri Portofolio</a>
                            <a href="{{ route('career.jobs') }}" class="inline-flex w-full items-center rounded-xl px-4 py-2 text-sm font-medium text-ink transition hover:bg-brand-mist">Lowongan Kerja</a>
                        </div>
                    </div>
                @endif
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
