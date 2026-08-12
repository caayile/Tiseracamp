@extends('layouts.admin')

@section('title', 'Galeri Portofolio')
@section('heading', 'Galeri Portofolio')

@section('content')
<div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
    <div class="card-soft min-w-0 overflow-hidden">
        <div class="flex flex-wrap items-center gap-2 border-b border-brand/10 px-5 py-4">
            <form method="GET" class="flex flex-wrap items-center gap-2">
                <select name="type" class="input-field w-auto" onchange="this.form.submit()">
                    <option value="">Semua tipe</option>
                    <option value="portfolio" @selected(request('type') === 'portfolio')>Portofolio</option>
                    <option value="cv" @selected(request('type') === 'cv')>CV</option>
                </select>
                <input type="search" name="q" value="{{ $search }}" placeholder="Cari judul atau pembuat"
                       class="input-field w-56">
                <button class="btn-primary" type="submit">Cari</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-sm">
                <thead class="bg-brand-mist/60 text-ink-soft">
                    <tr>
                        <th class="px-4 py-3 font-medium">Judul</th>
                        <th class="px-4 py-3 font-medium">Pembuat</th>
                        <th class="px-4 py-3 font-medium">Tipe</th>
                        <th class="px-4 py-3 font-medium">Tautan</th>
                        <th class="px-4 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($portfolios as $portfolio)
                        <tr class="border-t border-brand/10">
                            <td class="max-w-[260px] px-4 py-3">
                                <p class="truncate font-medium text-ink" title="{{ $portfolio->title }}">{{ $portfolio->title }}</p>
                                @if ($portfolio->description)
                                    <p class="mt-0.5 line-clamp-1 text-xs text-ink-soft">{{ $portfolio->description }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-ink-soft">{{ $portfolio->user->name }}</td>
                            <td class="px-4 py-3"><span class="badge">{{ $portfolio->typeLabel() }}</span></td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    @if ($portfolio->project_url)
                                        <a href="{{ $portfolio->project_url }}" target="_blank" class="btn-ghost text-xs">Project</a>
                                    @endif
                                    @if ($portfolio->portfolio_file_url)
                                        <a href="{{ media_url($portfolio->portfolio_file_url) }}" target="_blank" class="btn-ghost text-xs">PDF</a>
                                    @endif
                                    @if (! $portfolio->project_url && ! $portfolio->portfolio_file_url)
                                        <span class="text-xs text-ink-soft">—</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end">
                                    <form method="POST" action="{{ route('admin.portfolios.destroy', $portfolio) }}" onsubmit="return confirm('Hapus portofolio ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn-ghost text-xs text-red-600" type="submit">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-sm text-ink-soft">Belum ada portofolio.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4">{{ $portfolios->links() }}</div>
    </div>

    <form method="POST" action="{{ route('admin.portfolios.store') }}" enctype="multipart/form-data" class="card-soft h-fit space-y-4 p-5">
        @csrf
        <h2 class="font-display text-lg font-semibold">Tambah portofolio</h2>

        <div>
            <label class="mb-1 block text-sm font-medium">Pembuat</label>
            <select name="user_id" class="input-field" required>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected(old('user_id', auth()->id()) == $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium">Tipe</label>
            <select name="type" class="input-field" required>
                <option value="portfolio" @selected(old('type', 'portfolio') === 'portfolio')>Portofolio</option>
                <option value="cv" @selected(old('type') === 'cv')>CV</option>
            </select>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium">Judul</label>
            <input type="text" name="title" class="input-field" placeholder="Contoh: Portfolio UI Design" required value="{{ old('title') }}">
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium">Link project <span class="font-normal text-ink-soft">(opsional)</span></label>
            <input type="url" name="project_url" class="input-field" placeholder="https://..." value="{{ old('project_url') }}">
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium">File PDF <span class="font-normal text-ink-soft">(opsional, maks 5 MB)</span></label>
            <input type="file" name="portfolio_file" accept="application/pdf,.pdf" class="input-field file:mr-3 file:rounded-lg file:border-0 file:bg-brand/20 file:px-3 file:py-1.5 file:text-xs file:font-semibold">
            @error('portfolio_file') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium">Deskripsi <span class="font-normal text-ink-soft">(opsional)</span></label>
            <textarea name="description" rows="3" class="input-field" placeholder="Catatan singkat">{{ old('description') }}</textarea>
        </div>

        <button class="btn-primary w-full" type="submit">Simpan portofolio</button>
    </form>
</div>
@endsection
