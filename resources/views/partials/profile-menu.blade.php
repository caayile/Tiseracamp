@auth
@php
    $navUser = auth()->user();
    $navInitials = collect(explode(' ', $navUser->name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
@endphp

<div class="relative" data-profile-menu>
    <button type="button"
            data-profile-toggle
            class="flex items-center gap-2 rounded-full border border-ink/10 bg-white p-0.5 pr-2 shadow-sm transition hover:border-brand/40 hover:shadow-md"
            aria-expanded="false"
            aria-label="Menu profil">
        @if ($navUser->avatar)
            <img src="{{ asset('storage/'.$navUser->avatar) }}" alt="{{ $navUser->name }}" class="h-9 w-9 rounded-full object-cover ring-2 ring-brand/30">
        @else
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-[#0B1F2A] font-display text-xs font-bold text-brand">
                {{ strtoupper($navInitials) }}
            </span>
        @endif
        <svg class="h-4 w-4 text-ink-soft" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
        </svg>
    </button>

    <div class="absolute right-0 z-50 mt-2 hidden w-64 overflow-hidden rounded-2xl border border-brand/15 bg-white shadow-xl" data-profile-panel>
        <div class="border-b border-ink/8 bg-brand-mist/50 px-4 py-3">
            <p class="truncate text-sm font-semibold text-ink">{{ $navUser->name }}</p>
            <p class="truncate text-xs text-ink-soft">{{ $navUser->email }}</p>
        </div>
        <div class="p-2">
            <a href="{{ route('profile.edit') }}" class="block rounded-xl px-3 py-2.5 text-sm font-medium text-ink transition hover:bg-brand/10">
                Edit profil
            </a>
            @if ($navUser->isStudent())
                <a href="{{ route('profile.applications') }}" class="block rounded-xl px-3 py-2.5 text-sm font-medium text-ink transition hover:bg-brand/10">
                    Riwayat pendaftaran
                </a>
                <a href="{{ route('profile.logbook') }}" class="block rounded-xl px-3 py-2.5 text-sm font-medium text-ink transition hover:bg-brand/10">
                    Logbook
                </a>
            @endif
            <form method="POST" action="{{ route('logout') }}" class="border-t border-ink/8 mt-1 pt-1">
                @csrf
                <button type="submit" class="w-full rounded-xl px-3 py-2.5 text-left text-sm font-medium text-red-600 transition hover:bg-red-50">
                    Logout
                </button>
            </form>
        </div>
    </div>
</div>
@endauth
