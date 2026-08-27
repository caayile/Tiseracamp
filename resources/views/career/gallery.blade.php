@extends('layouts.app')

@section('title', 'Galeri Portofolio')

@section('content')
<section class="hero-gradient border-b border-brand/10">
    <div class="mx-auto max-w-6xl px-4 py-14">
        <p class="font-display text-sm font-bold uppercase tracking-[0.28em] text-brand-dark">Karier</p>
        <h1 class="mt-2 font-display text-3xl font-extrabold tracking-tight text-ink sm:text-4xl">Galeri Portofolio</h1>
        <p class="mt-2 max-w-2xl font-sans text-sm leading-relaxed text-ink-soft sm:text-[15px]">
            Unggah CV dan portofolio hasil proyekmu di sini. Saat daftar magang, data ini otomatis terisi di formulir.
        </p>
    </div>
</section>

<section class="mx-auto max-w-6xl px-4 py-10">
    <div id="portfolio-upload" class="card-soft p-6">
        <h2 class="font-display text-lg font-semibold text-ink">Unggah CV / portofolio</h2>
        <p class="mt-1 text-sm text-ink-soft">Pilih jenis dokumen, lalu simpan. Nanti tinggal dicek saat kirim pendaftaran.</p>

        @if ($errors->any())
            <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <p class="font-semibold">Terjadi kesalahan saat mengunggah:</p>
                <ul class="mt-1.5 list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('career.portfolio.store') }}" enctype="multipart/form-data" class="mt-4 grid gap-3 md:grid-cols-2" id="portfolio-form">
            @csrf

            {{-- Jenis --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink">Jenis</label>
                <select name="type" id="portfolio-type" class="input-field" required>
                    <option value="portfolio" @selected(old('type', 'portfolio') === 'portfolio')>Portofolio</option>
                    <option value="cv" @selected(old('type') === 'cv')>CV</option>
                </select>
            </div>

            {{-- Judul --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink">Judul</label>
                <input type="text" name="title" class="input-field" placeholder="Contoh: Aplikasi Web Todo / CV 2026" required value="{{ old('title') }}">
            </div>

            {{-- Gambar Proyek (wajib untuk portfolio) --}}
            <div class="md:col-span-2" id="image-field-wrap">
                <label class="mb-1.5 block text-sm font-medium text-ink">
                    Gambar proyek
                    <span id="image-required-badge" class="ml-1 rounded bg-red-100 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-red-600">Wajib</span>
                </label>
                <div class="relative">
                    <input type="file" name="project_image" id="project_image" accept="image/*,.jpg,.jpeg,.png,.webp,.gif,.svg,.bmp"
                           class="input-field file:mr-3 file:rounded-lg file:border-0 file:bg-brand/20 file:px-3 file:py-1.5 file:text-xs file:font-semibold"
                           onchange="previewImage(this)">
                    {{-- Preview gambar --}}
                    <div id="image-preview-wrap" class="mt-2 hidden">
                        <img id="image-preview" src="#" alt="Preview" class="h-36 w-auto rounded-xl border border-brand/15 object-cover shadow">
                    </div>
                </div>
                <p class="mt-1 text-xs text-ink-soft">JPG, PNG, WebP, GIF, SVG, BMP, <strong>maksimal 10 MB</strong>. Gambar akan muncul di beranda & galeri.</p>
                <p id="image-size-error" class="mt-1 hidden text-xs font-semibold text-red-600"></p>
                @error('project_image') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Link proyek --}}
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-ink">Link proyek <span class="font-normal text-ink-soft">(opsional)</span></label>
                <input type="url" name="project_url" class="input-field" placeholder="https://github.com/... atau https://..." value="{{ old('project_url') }}">
            </div>

            {{-- File PDF --}}
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-ink">
                    File PDF
                    <span id="pdf-optional-badge" class="font-normal text-ink-soft">(opsional untuk portofolio, disarankan untuk CV)</span>
                </label>
                <input type="file" name="portfolio_file" accept="application/pdf,.pdf" class="input-field file:mr-3 file:rounded-lg file:border-0 file:bg-brand/20 file:px-3 file:py-1.5 file:text-xs file:font-semibold">
                @error('portfolio_file') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Deskripsi --}}
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-ink">Deskripsi <span class="font-normal text-ink-soft">(opsional)</span></label>
                <textarea name="description" rows="2" class="input-field" placeholder="Deskripsikan proyekmu singkat...">{{ old('description') }}</textarea>
            </div>

            <button class="btn-primary md:col-span-2 md:w-fit" type="submit">Simpan dokumen</button>
        </form>

        @if (($myCvs ?? collect())->isNotEmpty() || ($myPortfolios ?? collect())->isNotEmpty())
            <div class="mt-8 grid gap-6 lg:grid-cols-2">
                {{-- CV tersimpan --}}
                <div>
                    <h3 class="font-display text-sm font-semibold uppercase tracking-wide text-brand-mid">CV tersimpan</h3>
                    <div class="mt-3 space-y-3">
                        @forelse ($myCvs as $cv)
                            <div class="rounded-xl border border-brand/15 p-4">
                                <p class="font-semibold text-ink">{{ $cv->title }}</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @if ($cv->project_url)
                                        <a href="{{ $cv->project_url }}" target="_blank" class="btn-ghost text-xs">Buka link</a>
                                    @endif
                                    @if ($cv->portfolio_file_url)
                                        <a href="{{ media_url($cv->portfolio_file_url) }}" target="_blank" class="btn-ghost text-xs">Lihat PDF</a>
                                    @endif
                                    <form method="POST" action="{{ route('career.portfolio.destroy', $cv) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn-ghost text-xs text-red-600" type="submit">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-ink-soft">Belum ada CV. Unggah agar otomatis terisi saat daftar.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Portofolio tersimpan --}}
                <div>
                    <h3 class="font-display text-sm font-semibold uppercase tracking-wide text-brand-mid">Portofolio tersimpan</h3>
                    <div class="mt-3 space-y-3">
                        @forelse ($myPortfolios as $portfolio)
                            <div class="rounded-xl border border-brand/15 p-4">
                                {{-- Thumbnail gambar --}}
                                @if ($portfolio->image_path)
                                    <img src="{{ media_url($portfolio->image_path) }}" alt="{{ $portfolio->title }}"
                                         class="mb-3 h-32 w-full rounded-lg object-cover">
                                @endif
                                <p class="font-semibold text-ink">{{ $portfolio->title }}</p>
                                @if ($portfolio->description)
                                    <p class="mt-1 text-sm text-ink-soft">{{ $portfolio->description }}</p>
                                @endif
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @if ($portfolio->project_url)
                                        <a href="{{ $portfolio->project_url }}" target="_blank" rel="noopener" class="btn-ghost text-xs">Lihat Link</a>
                                    @endif
                                    @if ($portfolio->portfolio_file_url)
                                        <a href="{{ media_url($portfolio->portfolio_file_url) }}" target="_blank" rel="noopener" class="btn-ghost text-xs text-rose-600">Lihat PDF</a>
                                    @endif
                                    <form method="POST" action="{{ route('career.portfolio.destroy', $portfolio) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn-ghost text-xs text-red-600" type="submit">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-ink-soft">Belum ada portofolio.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- ===== Galeri semua portofolio — auto-scroll ke kiri ===== --}}
    <div class="mt-12">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="font-display text-sm font-bold uppercase tracking-[0.28em] text-brand-mid">Portofolio</p>
                <h2 class="mt-2 font-display text-2xl font-bold tracking-tight text-ink">Karya peserta</h2>
            </div>

            <form method="GET" class="w-full sm:w-80">
                <div class="relative">
                    <input type="search" name="q" value="{{ $search ?? '' }}" placeholder="Cari judul atau pembuat"
                           class="input-field w-full py-3 pl-4 pr-12 text-sm">
                    <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg bg-brand px-3 py-2 text-xs font-semibold text-ink transition hover:bg-brand-light">
                        Cari
                    </button>
                </div>
            </form>
        </div>

        @if ($portfolios->isEmpty())
            <div class="mt-8 rounded-2xl border border-dashed border-brand/25 bg-panel px-6 py-12 text-center text-sm text-ink-soft">
                Belum ada portofolio yang ditampilkan.
            </div>
        @else
            {{-- AUTO-SCROLL marquee ke kiri (infinite loop) --}}
            @if (!($search ?? ''))
                <div class="portfolio-gallery-marquee relative mt-8 overflow-hidden" aria-label="Galeri portofolio peserta">
                    {{-- Fade kiri & kanan --}}
                    <div class="pointer-events-none absolute inset-y-0 left-0 z-10 w-16 bg-gradient-to-r from-[var(--color-bg,#f7fbfc)] to-transparent"></div>
                    <div class="pointer-events-none absolute inset-y-0 right-0 z-10 w-16 bg-gradient-to-l from-[var(--color-bg,#f7fbfc)] to-transparent"></div>

                    <div class="portfolio-gallery-track flex w-max gap-5 py-3">
                        @foreach ([1, 2] as $copy)
                            @foreach ($portfolios as $portfolio)
                                <div @if ($copy === 2) aria-hidden="true" @endif
                                     class="portfolio-gallery-item group w-64 shrink-0 overflow-hidden rounded-2xl border border-brand/10 bg-panel shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg sm:w-72">

                                    {{-- Gambar proyek --}}
                                    @if ($portfolio->image_path)
                                        <div class="relative h-40 overflow-hidden bg-brand-mist/40">
                                            <img src="{{ media_url($portfolio->image_path) }}"
                                                 alt="{{ $portfolio->title }}"
                                                 class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                                 loading="lazy">
                                        </div>
                                    @else
                                        <div class="flex h-40 items-center justify-center bg-gradient-to-br from-brand-mist to-brand/15">
                                            <svg class="h-10 w-10 text-brand/30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 18h16.5M3.75 3h16.5a.75.75 0 0 1 .75.75v13.5a.75.75 0 0 1-.75.75H3.75a.75.75 0 0 1-.75-.75V3.75a.75.75 0 0 1 .75-.75Z"/>
                                            </svg>
                                        </div>
                                    @endif

                                    <div class="p-4">
                                        {{-- Badge + nama --}}
                                        <div class="mb-2 flex items-center gap-2">
                                            <span class="rounded-full bg-brand-mist px-2 py-0.5 font-display text-[10px] font-bold uppercase tracking-wider text-brand-mid">Portofolio</span>
                                        </div>
                                        <h3 class="font-display text-sm font-bold leading-snug text-ink line-clamp-1">{{ $portfolio->title }}</h3>
                                        @if ($portfolio->description)
                                            <p class="mt-1 text-[12.5px] leading-relaxed text-ink-soft line-clamp-2">{{ $portfolio->description }}</p>
                                        @endif

                                        {{-- User info + tombol --}}
                                        <div class="mt-3 space-y-2 border-t border-ink/6 pt-2.5">
                                            <div class="flex items-center gap-2">
                                                @if ($portfolio->user?->avatar)
                                                    <img src="{{ media_url($portfolio->user->avatar) }}" alt="" class="h-6 w-6 shrink-0 rounded-full object-cover ring-1 ring-brand/20">
                                                @else
                                                    @php
                                                        $initials = collect(explode(' ', $portfolio->user?->name ?? 'TS'))->map(fn($w) => mb_substr($w,0,1))->take(2)->implode('');
                                                    @endphp
                                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand/20 font-display text-[9px] font-bold text-brand-mid">{{ strtoupper($initials) }}</span>
                                                @endif
                                                <p class="min-w-0 flex-1 truncate text-xs font-semibold text-ink">{{ $portfolio->user?->name ?? 'Peserta' }}</p>
                                            </div>

                                            @if ($portfolio->project_url || $portfolio->portfolio_file_url)
                                                <div class="flex flex-wrap items-center gap-1.5 pt-0.5">
                                                    @if ($portfolio->project_url)
                                                        <a href="{{ $portfolio->project_url }}" target="_blank" rel="noopener"
                                                           class="inline-flex items-center gap-1 rounded-md bg-brand/15 px-2 py-1 text-[11px] font-semibold text-brand-mid hover:bg-brand/30"
                                                           title="Buka Link Proyek">
                                                            <svg class="h-3 w-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                                                            </svg>
                                                            <span>Lihat Link</span>
                                                        </a>
                                                    @endif

                                                    @if ($portfolio->portfolio_file_url)
                                                        <a href="{{ media_url($portfolio->portfolio_file_url) }}" target="_blank" rel="noopener"
                                                           class="inline-flex items-center gap-1 rounded-md border border-rose-200 bg-rose-50 px-2 py-1 text-[11px] font-semibold text-rose-700 hover:bg-rose-100"
                                                           title="Buka Dokumen PDF">
                                                            <svg class="h-3 w-3 shrink-0 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                                                            </svg>
                                                            <span>Lihat PDF</span>
                                                        </a>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>

                {{-- Link ke semua --}}
                <div class="mt-6 text-center">
                    <a href="{{ route('career.gallery') }}?q=" class="btn-ghost text-sm">
                        Lihat semua &rarr;
                    </a>
                </div>
            @else
                {{-- Mode pencarian: grid biasa --}}
                <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($portfolios as $portfolio)
                        <article class="overflow-hidden rounded-2xl border border-brand/10 bg-panel transition duration-300 hover:-translate-y-1 hover:shadow-lg">
                            @if ($portfolio->image_path)
                                <div class="relative h-44 overflow-hidden">
                                    <img src="{{ media_url($portfolio->image_path) }}"
                                         alt="{{ $portfolio->title }}"
                                         class="h-full w-full object-cover">
                                </div>
                            @endif
                            <div class="p-5">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-brand-dark">{{ $portfolio->user->name }}</p>
                                <h3 class="mt-1 font-display text-base font-semibold text-ink">{{ $portfolio->title }}</h3>
                                <p class="mt-2 text-sm leading-6 text-ink-soft line-clamp-3">{{ $portfolio->description ?: 'Deskripsi belum tersedia.' }}</p>
                                <div class="mt-4 flex flex-wrap items-center gap-2">
                                    @if ($portfolio->project_url)
                                        <a href="{{ $portfolio->project_url }}" target="_blank" rel="noopener"
                                           class="inline-flex items-center gap-1.5 rounded-lg bg-brand/15 px-3 py-1.5 text-xs font-semibold text-brand-mid transition hover:bg-brand/30">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                                            </svg>
                                            <span>Lihat Link</span>
                                        </a>
                                    @endif
                                    @if ($portfolio->portfolio_file_url)
                                        <a href="{{ media_url($portfolio->portfolio_file_url) }}" target="_blank" rel="noopener"
                                           class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">
                                            <svg class="h-3.5 w-3.5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                                            </svg>
                                            <span>Lihat PDF</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-10 flex justify-center">
                    {{ $portfolios->links() }}
                </div>
            @endif
        @endif
    </div>
</section>

{{-- CSS & JS untuk auto-scroll marquee galeri --}}
<style>
    .portfolio-gallery-track {
        animation: portfolioScrollLeft 40s linear infinite;
    }

    .portfolio-gallery-marquee:hover .portfolio-gallery-track {
        animation-play-state: paused;
    }

    @keyframes portfolioScrollLeft {
        0%   { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
</style>

<script>
    // Toggle tampilan field gambar sesuai jenis
    (function () {
        const typeSelect = document.getElementById('portfolio-type');
        const imageWrap  = document.getElementById('image-field-wrap');
        const imageInput = document.getElementById('project_image');
        const badge      = document.getElementById('image-required-badge');
        const pdfBadge   = document.getElementById('pdf-optional-badge');
        const form       = document.getElementById('portfolio-form');

        function toggleImage() {
            const isPortfolio = typeSelect.value === 'portfolio';
            if (imageWrap) imageWrap.style.display = isPortfolio ? '' : 'none';
            if (badge) badge.style.display = isPortfolio ? '' : 'none';
            if (pdfBadge) {
                pdfBadge.textContent = isPortfolio
                    ? '(opsional untuk portofolio, maks 10 MB)'
                    : '(disarankan untuk CV, maks 10 MB)';
            }
        }

        typeSelect?.addEventListener('change', toggleImage);
        toggleImage(); // init

        form?.addEventListener('submit', function (e) {
            const isPortfolio = typeSelect.value === 'portfolio';

            if (isPortfolio) {
                if (!imageInput.files || imageInput.files.length === 0) {
                    e.preventDefault();
                    alert('Silakan pilih file gambar proyek terlebih dahulu sebelum menyimpan.');
                    imageInput.focus();
                    return false;
                }

                const file = imageInput.files[0];
                const maxBytes = 10 * 1024 * 1024; // 10 MB
                if (file.size > maxBytes) {
                    e.preventDefault();
                    alert('Ukuran gambar terlalu besar (' + (file.size / (1024 * 1024)).toFixed(1) + ' MB). Maksimal ukuran file adalah 10 MB.');
                    return false;
                }
            }

            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Sedang menyimpan...';
            }
        });
    })();

    // Preview gambar sebelum upload & validasi ukuran 10MB
    function previewImage(input) {
        const wrap = document.getElementById('image-preview-wrap');
        const img  = document.getElementById('image-preview');
        const errEl = document.getElementById('image-size-error');

        if (errEl) errEl.classList.add('hidden');

        if (input.files && input.files[0]) {
            const file = input.files[0];
            const maxBytes = 10 * 1024 * 1024; // 10 MB

            if (file.size > maxBytes) {
                if (errEl) {
                    errEl.textContent = 'Ukuran file ' + (file.size / (1024 * 1024)).toFixed(1) + ' MB melebihi batas 10 MB. Silakan pilih gambar yang lebih kecil.';
                    errEl.classList.remove('hidden');
                }
                input.value = '';
                wrap.classList.add('hidden');
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                img.src = e.target.result;
                wrap.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        } else {
            wrap.classList.add('hidden');
        }
    }
</script>
@endsection
