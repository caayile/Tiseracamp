<footer class="mt-20 bg-ink text-white">
    <div class="mx-auto grid max-w-6xl gap-10 px-4 py-14 md:grid-cols-3 md:gap-12">
        <div>
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                <x-brand-logo class="h-12 w-auto brightness-0 invert" />
                <span class="leading-tight">
                    <span class="block text-sm font-medium text-white/90">Magang</span>
                    <span class="block text-lg font-bold tracking-tight">Tiga Serangkai</span>
                </span>
            </a>
            <p class="mt-4 max-w-sm text-sm leading-relaxed text-white/80">
                Program ini dikelola langsung oleh unit Center of Excellent, yang berfokus pada pelatihan, pembelajaran, dan peningkatan kompetensi mahasiswa melalui pengalaman kerja nyata di dunia industri.
            </p>
            <p class="mt-5 flex items-start gap-2 text-sm leading-relaxed text-white/75">
                <svg class="mt-0.5 h-4 w-4 shrink-0 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M12 21s-7-4.5-7-10a7 7 0 1 1 14 0c0 5.5-7 10-7 10Z"/>
                    <circle cx="12" cy="11" r="2.5"/>
                </svg>
                <span>Jl. Prof. DR. Supomo No.23, Sriwedari, Kec. Laweyan, Kota Surakarta, Jawa Tengah 57141</span>
            </p>
        </div>

        <div>
            <p class="text-base font-bold text-white">Navigasi</p>
            <nav class="mt-4 flex flex-col gap-2.5 text-sm text-white/80">
                <a href="{{ route('home') }}" class="transition hover:text-brand">Beranda</a>
                <a href="{{ route('programs.index') }}" class="transition hover:text-brand">Bootcamp & Program</a>
                <a href="{{ route('programs.index', ['type' => 'internship']) }}" class="transition hover:text-brand">Magang</a>
                <a href="{{ route('news.index') }}" class="transition hover:text-brand">Berita</a>
                @auth
                    @if (auth()->user()->isStudent())
                        <a href="{{ route('career.gallery') }}" class="transition hover:text-brand">Karier</a>
                    @endif
                @endauth
            </nav>
        </div>

        <div>
            <p class="text-base font-bold text-white">Kontak</p>
            <a href="mailto:hspratita@tigaserangkai.co.id" class="mt-4 flex items-center gap-2 text-sm text-white/80 transition hover:text-brand">
                <svg class="h-4 w-4 shrink-0 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <rect x="3" y="5" width="18" height="14" rx="2"/>
                    <path d="m3 7 9 6 9-6"/>
                </svg>
                <span>Email: hspratita@tigaserangkai.co.id</span>
            </a>

            <div class="mt-6 flex items-center gap-4">
                <a href="https://www.linkedin.com/company/pt-tiga-serangkai-pustaka-mandiri/" target="_blank" rel="noopener noreferrer" class="text-white/85 transition hover:text-brand" aria-label="LinkedIn">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M4.98 3.5C4.98 4.88 3.86 6 2.5 6S0 4.88 0 3.5 1.12 1 2.5 1s2.48 1.12 2.48 2.5zM.5 8.5h4V23h-4V8.5zM8.5 8.5h3.8v2h.05c.53-1 1.83-2.05 3.77-2.05 4.03 0 4.78 2.65 4.78 6.1V23h-4v-6.6c0-1.57-.03-3.6-2.2-3.6-2.2 0-2.54 1.72-2.54 3.5V23h-4V8.5z"/>
                    </svg>
                </a>
                <a href="https://www.instagram.com/tigaserangkai" target="_blank" rel="noopener noreferrer" class="text-white/85 transition hover:text-brand" aria-label="Instagram">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <rect x="3" y="3" width="18" height="18" rx="5"/>
                        <circle cx="12" cy="12" r="4"/>
                        <circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/>
                    </svg>
                </a>
                <a href="https://www.tigaserangkai.com/" target="_blank" rel="noopener noreferrer" class="text-white/85 transition hover:text-brand" aria-label="Website Tiga Serangkai">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M3 12h18M12 3c2.5 2.8 3.8 5.8 3.8 9s-1.3 6.2-3.8 9c-2.5-2.8-3.8-5.8-3.8-9s1.3-6.2 3.8-9Z"/>
                    </svg>
                </a>
                <a href="https://www.tiktok.com/@magangts" target="_blank" rel="noopener noreferrer" class="text-white/85 transition hover:text-brand" aria-label="TikTok">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M16.6 5.82A4.87 4.87 0 0 1 14.9 2h-3.23v12.4a2.42 2.42 0 1 1-1.7-2.31V8.8a5.66 5.66 0 0 0-1.1-.1A5.57 5.57 0 1 0 14.4 14.3V8.7a8.1 8.1 0 0 0 4.7 1.5V7c-1.05 0-2.05-.4-2.5-1.18Z"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <div class="border-t border-white/10 py-5 text-center text-sm text-white/70">
        © {{ date('Y') }} PT Tiga Serangkai. All rights reserved.
    </div>
</footer>
