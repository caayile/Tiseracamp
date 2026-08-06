@extends('layouts.app')

@section('title', 'Galeri Portofolio')

@section('content')
<section class="hero-gradient border-b border-brand/10">
    <div class="mx-auto max-w-6xl px-4 py-14">
        <p class="font-display text-sm font-bold uppercase tracking-[0.28em] text-brand-dark">Karier</p>
        <h1 class="mt-2 font-display text-3xl font-extrabold tracking-tight text-ink sm:text-4xl">Galeri Portofolio</h1>
        <p class="mt-2 max-w-2xl font-sans text-sm leading-relaxed text-ink-soft sm:text-[15px]">
            Unggah CV dan portofolio di sini. Saat daftar magang, data ini otomatis terisi di formulir.
        </p>
    </div>
</section>

<section class="mx-auto max-w-6xl px-4 py-10">
    <div id="portfolio-upload" class="card-soft p-6">
        <h2 class="font-display text-lg font-semibold text-ink">Unggah CV / portofolio</h2>
        <p class="mt-1 text-sm text-ink-soft">Pilih jenis dokumen, lalu simpan. Nanti tinggal dicek saat kirim pendaftaran.</p>

        <form method="POST" action="{{ route('career.portfolio.store') }}" enctype="multipart/form-data" class="mt-4 grid gap-3 md:grid-cols-2">
            @csrf
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink">Jenis</label>
                <select name="type" class="input-field" required>
                    <option value="portfolio" @selected(old('type', 'portfolio') === 'portfolio')>Portofolio</option>
                    <option value="cv" @selected(old('type') === 'cv')>CV</option>
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink">Judul</label>
                <input type="text" name="title" class="input-field" placeholder="Contoh: CV 2026 / Portfolio UI Design" required value="{{ old('title') }}">
            </div>
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-ink">Link <span class="font-normal text-ink-soft">(opsional)</span></label>
                <input type="url" name="project_url" class="input-field" placeholder="https://..." value="{{ old('project_url') }}">
            </div>
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-ink">File PDF <span class="font-normal text-ink-soft">(opsional untuk portofolio, disarankan untuk CV)</span></label>
                <input type="file" name="portfolio_file" accept="application/pdf,.pdf" class="input-field file:mr-3 file:rounded-lg file:border-0 file:bg-brand/20 file:px-3 file:py-1.5 file:text-xs file:font-semibold">
                @error('portfolio_file') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-ink">Deskripsi <span class="font-normal text-ink-soft">(opsional)</span></label>
                <textarea name="description" rows="2" class="input-field" placeholder="Catatan singkat">{{ old('description') }}</textarea>
            </div>
            <button class="btn-primary md:col-span-2 md:w-fit" type="submit">Simpan dokumen</button>
        </form>

        @if (($myCvs ?? collect())->isNotEmpty() || ($myPortfolios ?? collect())->isNotEmpty())
            <div class="mt-8 grid gap-6 lg:grid-cols-2">
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
                <div>
                    <h3 class="font-display text-sm font-semibold uppercase tracking-wide text-brand-mid">Portofolio tersimpan</h3>
                    <div class="mt-3 space-y-3">
                        @forelse ($myPortfolios as $portfolio)
                            <div class="rounded-xl border border-brand/15 p-4">
                                <p class="font-semibold text-ink">{{ $portfolio->title }}</p>
                                @if ($portfolio->description)
                                    <p class="mt-1 text-sm text-ink-soft">{{ $portfolio->description }}</p>
                                @endif
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @if ($portfolio->project_url)
                                        <a href="{{ $portfolio->project_url }}" target="_blank" class="btn-ghost text-xs">Lihat project</a>
                                    @endif
                                    @if ($portfolio->portfolio_file_url)
                                        <a href="{{ media_url($portfolio->portfolio_file_url) }}" target="_blank" class="btn-ghost text-xs">Lihat PDF</a>
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

    <div class="mt-10 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
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

    <div class="mt-8 grid gap-5 lg:grid-cols-2">
        @forelse ($portfolios as $portfolio)
            <article class="overflow-hidden rounded-2xl border border-brand/10 bg-panel">
                <div class="border-b border-brand/10 bg-brand-mist/50 px-5 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-brand-dark">{{ $portfolio->user->name }}</p>
                    <h3 class="mt-2 font-display text-lg font-semibold text-ink">{{ $portfolio->title }}</h3>
                </div>

                <div class="p-5">
                    <p class="text-sm leading-6 text-ink-soft line-clamp-3">{{ $portfolio->description ?: 'Deskripsi singkat belum tersedia.' }}</p>
                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        @if ($portfolio->project_url)
                            <a href="{{ $portfolio->project_url }}" target="_blank" class="btn-ghost text-xs">Lihat project</a>
                        @endif
                        @if ($portfolio->portfolio_file_url)
                            <a href="{{ media_url($portfolio->portfolio_file_url) }}" target="_blank" class="btn-ghost text-xs">Lihat PDF</a>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-brand/25 bg-panel px-6 py-10 text-center text-sm text-ink-soft">
                Belum ada portofolio yang ditampilkan.
            </div>
        @endforelse
    </div>

    <div class="mt-10 flex justify-center">
        {{ $portfolios->links() }}
    </div>
</section>
@endsection
