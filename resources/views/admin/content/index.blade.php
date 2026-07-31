@extends('layouts.admin')

@section('title', 'Content')
@section('heading', 'Kelola Konten')

@section('content')
<div class="grid gap-6 xl:grid-cols-2">
    {{-- Berita --}}
    <div class="card-soft p-5">
        <h2 class="font-display text-lg font-semibold">Berita</h2>
        <p class="mt-1 text-xs text-ink-soft">Hanya admin yang dapat menambah berita. Tampil di beranda & menu Berita.</p>
        <form method="POST" action="{{ route('admin.content.articles') }}" enctype="multipart/form-data" class="mt-4 space-y-3">
            @csrf
            <input type="text" name="title" class="input-field" placeholder="Judul berita" required>
            <input type="text" name="excerpt" class="input-field" placeholder="Ringkasan singkat">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink">Thumbnail <span class="font-normal text-ink-soft">(opsional)</span></label>
                <input type="file" name="thumbnail" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp" class="input-field file:mr-3 file:rounded-lg file:border-0 file:bg-brand/20 file:px-3 file:py-1.5 file:text-xs file:font-semibold">
                @error('thumbnail') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <textarea name="body" rows="4" class="input-field" placeholder="Isi berita"></textarea>
            <button class="btn-primary" type="submit">Publikasikan berita</button>
        </form>
        <ul class="mt-4 space-y-2">
            @forelse ($articles as $article)
                <li class="flex items-start justify-between gap-3 rounded-lg bg-brand-mist/50 px-3 py-2 text-sm">
                    <div class="flex min-w-0 items-start gap-3">
                        @if ($article->thumbnail)
                            <img src="{{ media_url($article->thumbnail) }}" alt="" class="h-12 w-16 shrink-0 rounded-lg object-cover">
                        @endif
                        <div class="min-w-0">
                            <p class="font-medium text-ink">{{ $article->title }}</p>
                            <p class="text-xs text-ink-soft">{{ $article->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.content.articles.destroy', $article) }}" onsubmit="return confirm('Hapus berita ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Hapus</button>
                    </form>
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
            <input type="text" name="cta_link" class="input-field" placeholder="Link CTA">
            <button class="btn-primary" type="submit">Tambah banner</button>
        </form>
        <ul class="mt-4 space-y-2">
            @foreach ($banners as $banner)
                <li class="rounded-lg bg-brand-mist/50 px-3 py-2 text-sm">{{ $banner->title }}</li>
            @endforeach
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
        <ul class="mt-4 space-y-2">
            @foreach ($faqs as $faq)
                <li class="rounded-lg bg-brand-mist/50 px-3 py-2 text-sm">
                    <p class="font-medium">{{ $faq->question }}</p>
                    <p class="text-xs text-ink-soft">{{ \Illuminate\Support\Str::limit($faq->answer, 80) }}</p>
                </li>
            @endforeach
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
            @foreach ($categories as $category)
                <li class="flex justify-between rounded-lg bg-brand-mist/50 px-3 py-2 text-sm">
                    <span>{{ $category->name }}</span>
                    <span class="text-xs text-ink-soft">{{ $category->programs_count }} program</span>
                </li>
            @endforeach
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
