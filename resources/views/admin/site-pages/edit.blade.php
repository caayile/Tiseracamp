@extends('layouts.admin')

@section('title', 'Syarat & Privasi')
@section('heading', 'Syarat & Privasi')

@section('content')
<div class="space-y-6">
    <p class="text-sm text-ink-soft">Teks ini tampil di halaman publik Syarat & Ketentuan dan Kebijakan Privasi. Siswa melihat versi terbaru setelah kamu simpan.</p>

    @foreach ($pages as $page)
        <form method="POST" action="{{ route('admin.site-pages.update', $page) }}" class="card-soft space-y-3 p-5">
            @csrf
            @method('PUT')
            <h2 class="font-display text-lg font-semibold">{{ $page->slug === 'terms' ? 'Syarat & Ketentuan' : 'Kebijakan Privasi' }}</h2>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink">Judul</label>
                <input type="text" name="title" value="{{ old('title_'.$page->id, $page->title) }}" class="input-field" required maxlength="160">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink">Isi</label>
                <textarea name="body" rows="12" class="input-field" required>{{ old('body_'.$page->id, $page->body) }}</textarea>
            </div>
            <button type="submit" class="btn-primary">Simpan {{ $page->slug === 'terms' ? 'syarat' : 'privasi' }}</button>
        </form>
    @endforeach
</div>
@endsection
