@extends('layouts.admin')

@section('title', 'Content')
@section('heading', 'Kelola Konten')

@section('content')
@php
    $editing = $editingArticle ?? null;
@endphp

<div class="grid gap-6 xl:grid-cols-2">
    {{-- Berita --}}
    <div class="card-soft p-5 xl:col-span-2">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="font-display text-lg font-semibold">Berita</h2>
                <p class="mt-1 text-xs text-ink-soft">
                    Field mengikuti halaman publik: judul, tanggal publikasi, gambar, lalu isi berita.
                </p>
            </div>
            @if ($editing)
                <a href="{{ route('admin.content.index') }}" class="text-sm font-semibold text-brand-mid hover:underline">Batal edit</a>
            @endif
        </div>

        <form method="POST"
              action="{{ $editing ? route('admin.content.articles.update', $editing) : route('admin.content.articles') }}"
              enctype="multipart/form-data"
              class="mt-5 grid gap-4 lg:grid-cols-[minmax(0,1fr)_280px]">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div class="space-y-3">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-ink">Judul</label>
                    <input type="text" name="title" value="{{ old('title', $editing?->title) }}" class="input-field" placeholder="Judul berita" required>
                    @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-ink">Dipublikasikan pada</label>
                    <input type="datetime-local"
                           name="published_at"
                           value="{{ old('published_at', optional($editing?->publishedAt())->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i')) }}"
                           class="input-field"
                           required>
                    @error('published_at') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-ink">Ringkasan <span class="font-normal text-ink-soft">(opsional)</span></label>
                    <input type="text" name="excerpt" value="{{ old('excerpt', $editing?->excerpt) }}" class="input-field" placeholder="Ringkasan singkat di bawah judul">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-ink">Isi berita</label>
                    <textarea name="body" rows="10" class="input-field" placeholder="Tulis isi berita lengkap..." required>{{ old('body', $editing?->body) }}</textarea>
                    @error('body') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-ink">Gambar</label>
                    @if ($editing?->thumbnail)
                        <img src="{{ media_url($editing->thumbnail) }}" alt="" class="mb-2 aspect-[16/10] w-full rounded-xl object-cover ring-1 ring-black/5">
                    @endif
                    <input type="file" name="thumbnail" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp" class="input-field file:mr-3 file:rounded-lg file:border-0 file:bg-brand/20 file:px-3 file:py-1.5 file:text-xs file:font-semibold">
                    <p class="mt-1 text-[11px] text-ink-soft">JPG/PNG/WebP, max 5MB. Tampil di kartu beranda & detail.</p>
                    @error('thumbnail') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <label class="flex items-center gap-2 rounded-xl border border-ink/10 bg-surface px-3 py-2.5 text-sm text-ink">
                    <input type="checkbox" name="is_published" value="1" class="rounded border-slate-300 text-brand focus:ring-brand" @checked(old('is_published', $editing?->is_published ?? true))>
                    Tampilkan di halaman Berita
                </label>

                <button class="btn-primary w-full justify-center" type="submit">
                    {{ $editing ? 'Simpan perubahan' : 'Publikasikan berita' }}
                </button>
            </div>
        </form>

        <ul class="mt-6 space-y-2 border-t border-brand/10 pt-4">
            @forelse ($articles as $article)
                <li class="flex items-start justify-between gap-3 rounded-xl bg-brand-mist/50 px-3 py-3 text-sm">
                    <div class="flex min-w-0 items-start gap-3">
                        @if ($article->thumbnail)
                            <img src="{{ media_url($article->thumbnail) }}" alt="" class="h-14 w-20 shrink-0 rounded-lg object-cover">
                        @endif
                        <div class="min-w-0">
                            <p class="font-medium text-ink">{{ $article->title }}</p>
                            <p class="mt-0.5 text-xs text-ink-soft">
                                Dipublikasikan pada {{ $article->publishedAt()->locale('id')->translatedFormat('l, d F Y') }}
                                @unless ($article->is_published)
                                    · <span class="font-semibold text-amber-700">Draft</span>
                                @endunless
                            </p>
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-3">
                        <a href="{{ route('admin.content.index', ['edit' => $article->id]) }}" class="text-xs font-semibold text-brand-mid hover:underline">Edit</a>
                        <a href="{{ route('news.show', $article->slug) }}" target="_blank" class="text-xs font-semibold text-ink-soft hover:underline">Lihat</a>
                        <form method="POST" action="{{ route('admin.content.articles.destroy', $article) }}" onsubmit="return confirm('Hapus berita ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Hapus</button>
                        </form>
                    </div>
                </li>
            @empty
                <li class="text-sm text-ink-soft">Belum ada berita.</li>
            @endforelse
        </ul>
    </div>

    {{-- Banner --}}
    <div class="card-soft p-5">
        <h2 class="font-display text-lg font-semibold">Banner</h2>
        <form method="POST" action="{{ route('admin.content.banners') }}" class="mt-4 space-y-3">
            @csrf
            <input type="text" name="title" class="input-field" placeholder="Judul banner" required>
            <input type="text" name="subtitle" class="input-field" placeholder="Subjudul">
            <input type="text" name="cta_text" class="input-field" placeholder="Teks CTA">
            <input type="text" name="cta_link" class="input-field" placeholder="Link CTA (/programs, https://...)">
            <button class="btn-primary" type="submit">Tambah banner</button>
        </form>
        <ul class="mt-4 space-y-3">
            @forelse ($banners as $banner)
                <li class="rounded-lg border border-brand/10 bg-brand-mist/40 p-3 text-sm">
                    <form method="POST" action="{{ route('admin.content.banners.update', $banner) }}" class="space-y-2">
                        @csrf
                        @method('PUT')
                        <input type="text" name="title" value="{{ $banner->title }}" class="input-field py-1 text-xs" required>
                        <input type="text" name="subtitle" value="{{ $banner->subtitle }}" class="input-field py-1 text-xs" placeholder="Subjudul">
                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" name="cta_text" value="{{ $banner->cta_text }}" class="input-field py-1 text-xs" placeholder="CTA">
                            <input type="text" name="cta_link" value="{{ $banner->cta_link }}" class="input-field py-1 text-xs" placeholder="Link">
                        </div>
                        <label class="flex items-center gap-2 text-xs">
                            <input type="checkbox" name="is_active" value="1" @checked($banner->is_active) class="rounded border-slate-300 text-brand focus:ring-brand">
                            Aktif di beranda
                        </label>
                        <div class="flex gap-2">
                            <button class="btn-primary text-xs" type="submit">Simpan</button>
                            <button form="banner-del-{{ $banner->id }}" class="btn-ghost text-xs text-red-600" type="submit" onclick="return confirm('Hapus banner?')">Hapus</button>
                        </div>
                    </form>
                    <form id="banner-del-{{ $banner->id }}" method="POST" action="{{ route('admin.content.banners.destroy', $banner) }}" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                </li>
            @empty
                <li class="text-sm text-ink-soft">Belum ada banner.</li>
            @endforelse
        </ul>
    </div>

    {{-- FAQ --}}
    <div class="card-soft p-5">
        <h2 class="font-display text-lg font-semibold">FAQ</h2>
        <form method="POST" action="{{ route('admin.content.faqs') }}" class="mt-4 space-y-3">
            @csrf
            <input type="text" name="question" class="input-field" placeholder="Pertanyaan" required>
            <textarea name="answer" rows="2" class="input-field" placeholder="Jawaban" required></textarea>
            <button class="btn-primary" type="submit">Tambah FAQ</button>
        </form>
        <ul class="mt-4 space-y-3">
            @forelse ($faqs as $faq)
                <li class="rounded-lg border border-brand/10 bg-brand-mist/40 p-3 text-sm">
                    <form method="POST" action="{{ route('admin.content.faqs.update', $faq) }}" class="space-y-2">
                        @csrf
                        @method('PUT')
                        <input type="text" name="question" value="{{ $faq->question }}" class="input-field py-1 text-xs" required>
                        <textarea name="answer" rows="2" class="input-field py-1 text-xs" required>{{ $faq->answer }}</textarea>
                        <label class="flex items-center gap-2 text-xs">
                            <input type="checkbox" name="is_published" value="1" @checked($faq->is_published) class="rounded border-slate-300 text-brand focus:ring-brand">
                            Tampil di beranda
                        </label>
                        <div class="flex gap-2">
                            <button class="btn-primary text-xs" type="submit">Simpan</button>
                            <button form="faq-del-{{ $faq->id }}" class="btn-ghost text-xs text-red-600" type="submit" onclick="return confirm('Hapus FAQ?')">Hapus</button>
                        </div>
                    </form>
                    <form id="faq-del-{{ $faq->id }}" method="POST" action="{{ route('admin.content.faqs.destroy', $faq) }}" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                </li>
            @empty
                <li class="text-sm text-ink-soft">Belum ada FAQ.</li>
            @endforelse
        </ul>
    </div>

    {{-- Kategori --}}
    <div class="card-soft p-5">
        <h2 class="font-display text-lg font-semibold">Kategori</h2>
        <form method="POST" action="{{ route('admin.content.categories') }}" class="mt-4 flex gap-2">
            @csrf
            <input type="text" name="name" class="input-field" placeholder="Nama kategori" required>
            <button class="btn-primary shrink-0" type="submit">Tambah</button>
        </form>
        <ul class="mt-4 space-y-2">
            @forelse ($categories as $category)
                <li class="rounded-lg border border-brand/10 bg-brand-mist/40 p-3 text-sm">
                    <form method="POST" action="{{ route('admin.content.categories.update', $category) }}" class="flex flex-wrap items-center gap-2">
                        @csrf
                        @method('PUT')
                        <input type="text" name="name" value="{{ $category->name }}" class="input-field py-1 text-xs flex-1" required>
                        <span class="text-xs text-ink-soft">{{ $category->programs_count }} program</span>
                        <button class="btn-primary text-xs" type="submit">Simpan</button>
                        <button form="cat-del-{{ $category->id }}" class="btn-ghost text-xs text-red-600" type="submit" onclick="return confirm('Hapus kategori?')">Hapus</button>
                    </form>
                    <form id="cat-del-{{ $category->id }}" method="POST" action="{{ route('admin.content.categories.destroy', $category) }}" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                </li>
            @empty
                <li class="text-sm text-ink-soft">Belum ada kategori.</li>
            @endforelse
        </ul>
    </div>

    {{-- Broadcast --}}
    <div class="card-soft p-5 xl:col-span-2">
        <h2 class="font-display text-lg font-semibold">Broadcast notifikasi</h2>
        <form method="POST" action="{{ route('admin.content.broadcast') }}" class="mt-4 grid gap-3 md:grid-cols-2">
            @csrf
            <input type="text" name="title" class="input-field" placeholder="Judul broadcast" required>
            <select name="audience" class="input-field" required>
                <option value="all">Semua (siswa + mentor)</option>
                <option value="student">Siswa saja</option>
                <option value="mentor">Mentor saja</option>
            </select>
            <textarea name="body" rows="3" class="input-field md:col-span-2" placeholder="Isi pesan" required></textarea>
            <button class="btn-primary md:col-span-2 md:w-fit" type="submit">Kirim broadcast</button>
        </form>
        @if ($announcements->isNotEmpty())
            <div class="mt-4 border-t border-brand/10 pt-4">
                <p class="text-sm font-medium text-ink-soft">Broadcast terakhir</p>
                @foreach ($announcements->take(3) as $announcement)
                    <p class="mt-2 text-sm">{{ $announcement->title }} — {{ $announcement->created_at->diffForHumans() }}</p>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
