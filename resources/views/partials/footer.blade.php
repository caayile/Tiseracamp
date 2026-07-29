<footer class="mt-20 border-t border-brand/10 bg-ink text-white">
    <div class="mx-auto grid max-w-6xl gap-8 px-4 py-12 md:grid-cols-3">
        <div>
            <x-brand-logo class="h-16 w-auto brightness-0 invert" />
            <p class="mt-3 max-w-sm text-sm leading-relaxed text-white/70">
                Platform bootcamp dan magang online untuk membangun skill, portfolio, dan jalur karier yang lebih jelas.
            </p>
        </div>
        <div>
            <p class="text-sm font-semibold uppercase tracking-wider text-brand-light">Jelajahi</p>
            <div class="mt-3 flex flex-col gap-2 text-sm text-white/75">
                <a href="{{ route('programs.index') }}" class="hover:text-brand-light">Semua Program</a>
                <a href="{{ route('programs.index', ['type' => 'bootcamp']) }}" class="hover:text-brand-light">Bootcamp</a>
                <a href="{{ route('programs.index', ['type' => 'internship']) }}" class="hover:text-brand-light">Magang Online</a>
            </div>
        </div>
        <div>
            <p class="text-sm font-semibold uppercase tracking-wider text-brand-light">Kontak</p>
            <p class="mt-3 text-sm text-white/75">hello@tigaserangkai.id</p>
            <p class="mt-1 text-sm text-white/75">Belajar. Praktik. Siap kerja.</p>
        </div>
    </div>
    <div class="border-t border-white/10 py-4 text-center text-xs text-white/50">
        © {{ date('Y') }} Tiga Serangkai. All rights reserved.
    </div>
</footer>
