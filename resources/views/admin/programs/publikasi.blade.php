@extends('layouts.admin')

@section('title', 'Publikasi Program')
@section('heading', 'Publikasi: '.$program->title)

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div class="rounded-2xl border border-brand/20 bg-brand-mist/50 px-4 py-3 text-sm text-ink-soft">
        Atur PIC/mentor, kategori, partner, dan status tampil di katalog. Detail lowongan/bootcamp diedit di halaman Edit.
    </div>

    <form method="POST" action="{{ route('admin.programs.publikasi.update', $program) }}" class="card-soft space-y-5 p-6">
        @csrf
        @method('PUT')

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-ink">PIC / Mentor</label>
                <select name="mentor_id" class="input-field">
                    <option value="">— Tidak ada —</option>
                    @foreach ($mentors as $mentor)
                        <option value="{{ $mentor->id }}" @selected(old('mentor_id', $program->mentor_id) == $mentor->id)>{{ $mentor->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-ink">Kategori</label>
                <select name="category_id" class="input-field">
                    <option value="">— Tidak ada —</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $program->category_id) == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-ink">Status approval</label>
                <select name="approval_status" class="input-field">
                    @foreach (['draft', 'pending', 'approved', 'rejected'] as $status)
                        <option value="{{ $status }}" @selected(old('approval_status', $program->approval_status) === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-ink">Partner</label>
                <select name="partner_id" class="input-field">
                    <option value="">— Tidak ada —</option>
                    @foreach ($partners as $partner)
                        <option value="{{ $partner->id }}" @selected(old('partner_id', $program->partner_id) == $partner->id)>{{ $partner->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-semibold text-ink">Ringkasan singkat</label>
            <textarea name="excerpt" rows="2" class="input-field">{{ old('excerpt', $program->excerpt) }}</textarea>
        </div>

        @if ($program->type === 'bootcamp')
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-ink">Deskripsi</label>
                <textarea name="description" rows="5" class="input-field">{{ old('description', $program->description) }}</textarea>
            </div>
        @endif

        <div class="flex flex-wrap gap-4">
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $program->is_published))>
                Published (tampil di katalog)
            </label>
            @if ($program->type === 'bootcamp')
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $program->is_featured))>
                    Featured (program unggulan)
                </label>
            @endif
        </div>

        <div class="flex flex-wrap gap-3 pt-2">
            <button class="btn-primary" type="submit">Simpan publikasi</button>
            <a href="{{ route('admin.programs.edit', $program) }}" class="btn-secondary">Edit detail</a>
            <a href="{{ route('admin.programs.index') }}" class="btn-secondary">Kembali</a>
        </div>
    </form>
</div>
@endsection
