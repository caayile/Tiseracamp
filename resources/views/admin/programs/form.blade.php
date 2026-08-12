@extends('layouts.admin')

@section('title', $program->exists
    ? 'Edit Program'
    : ($program->type === 'job' ? 'Tambah Lowongan Kerja' : ($program->type === 'bootcamp' ? 'Tambah Bootcamp' : 'Tambah Lowongan Magang')))
@section('heading', $program->exists
    ? ($program->type === 'internship' ? 'Edit Lowongan Magang' : ($program->type === 'job' ? 'Edit Lowongan Kerja' : 'Edit Bootcamp'))
    : ($program->type === 'job' ? 'Tambah Lowongan Kerja' : ($program->type === 'bootcamp' ? 'Tambah Bootcamp' : 'Tambah Lowongan Magang')))

@section('content')
@php
    $isBootcampEdit = $program->exists && $program->type === 'bootcamp';
    $isJobForm = $program->type === 'job';
    $isBootcampCreate = ! $program->exists && $program->type === 'bootcamp';
    $qualificationsText = old('qualifications_text', collect($program->qualifications ?? [])->implode("\n"));
@endphp

@if ($isBootcampEdit)
    <form method="POST" action="{{ route('admin.programs.update', $program) }}" class="mx-auto max-w-3xl space-y-6">
        @csrf
        @method('PUT')
        <input type="hidden" name="type" value="bootcamp">
        <input type="hidden" name="level" value="{{ $program->level }}">

        <div class="rounded-2xl border border-brand/20 bg-brand-mist/50 px-4 py-3 text-sm text-ink-soft">
            Bootcamp diajukan mentor. Admin bisa meninjau, mengedit detail, atau approve dari daftar program.
        </div>

        <section class="card-soft space-y-4 p-6">
            <div>
                <label class="mb-1.5 block text-sm font-semibold">Judul</label>
                <input type="text" name="title" value="{{ old('title', $program->title) }}" class="input-field" required>
            </div>
            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold">Durasi (bulan)</label>
                    <input type="number" name="duration_months" value="{{ old('duration_months', $program->duration_months) }}" class="input-field" min="1" required>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold">Harga (Rp)</label>
                    <input type="number" name="price" value="{{ old('price', $program->price) }}" class="input-field" min="0" required>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold">Status approval</label>
                    <select name="approval_status" class="input-field">
                        @foreach (['draft', 'pending', 'approved', 'rejected'] as $status)
                            <option value="{{ $status }}" @selected(old('approval_status', $program->approval_status) === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Mentor</label>
                    <select name="mentor_id" class="input-field">
                        <option value="">— Tidak ada —</option>
                        @foreach ($mentors as $mentor)
                            <option value="{{ $mentor->id }}" @selected(old('mentor_id', $program->mentor_id) == $mentor->id)>{{ $mentor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Kategori</label>
                    <select name="category_id" class="input-field">
                        <option value="">— Tidak ada —</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $program->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium">Partner / Perusahaan</label>
                <input type="text" name="partner_name" value="{{ old('partner_name', $program->partner?->name) }}" class="input-field" placeholder="Ketik nama perusahaan" />
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium">Ringkasan</label>
                <textarea name="excerpt" rows="2" class="input-field">{{ old('excerpt', $program->excerpt) }}</textarea>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium">Deskripsi</label>
                <textarea name="description" rows="4" class="input-field">{{ old('description', $program->description) }}</textarea>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium">Benefits (satu baris satu item)</label>
                <textarea name="benefits_text" rows="4" class="input-field">{{ old('benefits_text', collect($program->benefits ?? [])->implode("\n")) }}</textarea>
            </div>
            <div class="flex flex-wrap gap-4">
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $program->is_published))>
                    Published
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $program->is_featured))>
                    Featured
                </label>
            </div>
        </section>

        <div class="flex gap-3">
            <button class="btn-primary" type="submit">Simpan</button>
            <a href="{{ route('admin.programs.index', ['type' => 'bootcamp']) }}" class="btn-secondary">Batal</a>
        </div>
    </form>
@elseif ($isJobForm)
    <form method="POST"
          action="{{ $program->exists ? route('admin.programs.update', $program) : route('admin.programs.store') }}"
          class="mx-auto max-w-4xl space-y-6">
        @csrf
        @if ($program->exists)
            @method('PUT')
        @endif
        <input type="hidden" name="type" value="job">
        <input type="hidden" name="level" value="Intermediate">
        <input type="hidden" name="duration_months" value="0">

        <div class="rounded-2xl border border-brand/20 bg-brand-mist/50 px-4 py-3 text-sm text-ink-soft">
            Lowongan ini akan tampil di <strong class="text-ink">Karier → Lowongan Kerja</strong> setelah disimpan.
        </div>

        <section class="card-soft space-y-5 p-6">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-brand-dark">Detail lowongan kerja</p>
                <h2 class="mt-1 font-display text-lg font-semibold text-ink">Informasi yang dilihat pelamar</h2>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-ink">Judul lowongan <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $program->title) }}" class="input-field" placeholder="Contoh: Frontend Developer" required>
                @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-ink">Gaji (Rp)</label>
                    <input type="number" name="price" value="{{ old('price', $program->price ?? 0) }}" class="input-field" min="0" placeholder="0 = dirundingkan">
                    <p class="mt-1 text-xs text-ink-soft">Isi 0 jika gaji dirundingkan.</p>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-ink">Deadline lamaran</label>
                    <input type="date" name="deadline" value="{{ old('deadline', optional($program->deadline)->format('Y-m-d')) }}" class="input-field">
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-ink">Kategori</label>
                    <select name="category_id" class="input-field">
                        <option value="">— Pilih kategori —</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $program->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-ink">Partner / Perusahaan</label>
                    <input type="text" name="partner_name" value="{{ old('partner_name', $program->partner?->name) }}" class="input-field" placeholder="Ketik nama perusahaan" />
                </div>
            </div>

            <div class="rounded-2xl border border-brand/15 bg-gradient-to-br from-brand-mist/50 to-panel p-4">
                <p class="text-sm font-semibold text-ink">Sasaran pelamar</p>
                <p class="mt-0.5 text-xs text-ink-soft">Nyalakan toggle untuk Umum dan/atau TS Group. Jika keduanya menyala, lowongan tampil di kedua tempat tanpa diisi dua kali — dan jika keduanya dimatikan, lowongan tidak tampil di mana pun.</p>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <label class="flex cursor-pointer items-center justify-between gap-4 rounded-xl border border-brand/15 bg-panel px-4 py-3">
                        <span>
                            <span class="block text-sm font-semibold text-ink">Terbuka umum</span>
                            <span class="block text-xs text-ink-soft">Terlihat oleh semua pengguna.</span>
                        </span>
                        <input type="checkbox" data-audience-umum class="peer sr-only" @checked(in_array(old('audience', $program->audience ?? 'all'), ['all', 'both'], true))>
                        <span class="pointer-events-none relative inline-flex h-7 w-12 shrink-0 items-center rounded-full bg-ink/25 transition after:absolute after:left-1 after:top-1 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition-all after:content-[''] peer-checked:bg-brand peer-checked:after:translate-x-5"></span>
                    </label>
                    <label class="flex cursor-pointer items-center justify-between gap-4 rounded-xl border border-brand/15 bg-panel px-4 py-3">
                        <span>
                            <span class="block text-sm font-semibold text-ink">TS Group</span>
                            <span class="block text-xs text-ink-soft">Terlihat oleh mahasiswa TSU.</span>
                        </span>
                        <input type="checkbox" data-audience-tsu class="peer sr-only" @checked(old('audience', $program->audience ?? 'all') === 'tsu' || old('audience', $program->audience ?? 'all') === 'both')>
                        <span class="pointer-events-none relative inline-flex h-7 w-12 shrink-0 items-center rounded-full bg-ink/25 transition after:absolute after:left-1 after:top-1 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition-all after:content-[''] peer-checked:bg-brand peer-checked:after:translate-x-5"></span>
                    </label>
                </div>
                <input type="hidden" name="audience" value="{{ old('audience', $program->audience ?? 'all') }}">
                @error('audience') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-ink">Lokasi</label>
                <input type="text" name="location" value="{{ old('location', $program->location) }}" class="input-field" placeholder="Contoh: Surakarta / Remote">
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-ink">Ringkasan</label>
                <textarea name="excerpt" rows="2" class="input-field">{{ old('excerpt', $program->excerpt) }}</textarea>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-ink">Kualifikasi</label>
                <textarea name="qualifications_text" rows="4" class="input-field" placeholder="Satu baris = satu poin">{{ $qualificationsText }}</textarea>
            </div>
        </section>

        <section class="card-soft space-y-5 p-6">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-brand-dark">Halaman detail</p>
                <h2 class="mt-1 font-display text-lg font-semibold text-ink">Konten pada halaman detail lowongan</h2>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-ink">Deskripsi</label>
                <textarea name="description" rows="5" class="input-field" placeholder="Jelaskan tugas dan persyaratan pekerjaan...">{{ old('description', $program->description) }}</textarea>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-ink">Benefits / Keuntungan</label>
                <textarea name="benefits_text" rows="4" class="input-field" placeholder="BPJS, remote, mentoring...">{{ old('benefits_text', collect($program->benefits ?? [])->implode("\n")) }}</textarea>
            </div>
        </section>

        <div class="flex gap-3">
            <button class="btn-primary" type="submit">{{ $program->exists ? 'Simpan perubahan' : 'Simpan lowongan kerja' }}</button>
            <a href="{{ route('admin.programs.index', ['type' => 'job']) }}" class="btn-secondary">Batal</a>
        </div>
    </form>
@elseif ($isBootcampCreate)
    <form method="POST" action="{{ route('admin.programs.store') }}" class="mx-auto max-w-4xl space-y-6">
        @csrf
        <input type="hidden" name="type" value="bootcamp">
        <input type="hidden" name="level" value="Intermediate">

        <div class="rounded-2xl border border-brand/20 bg-brand-mist/50 px-4 py-3 text-sm text-ink-soft">
            Tambah bootcamp ke katalog. Kurikulum bisa diatur setelah program dibuat.
        </div>

        <section class="card-soft space-y-5 p-6">
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-ink">Judul bootcamp <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $program->title) }}" class="input-field" required>
                @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-ink">Durasi (bulan)</label>
                    <input type="number" name="duration_months" value="{{ old('duration_months', 3) }}" class="input-field" min="1" required>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-ink">Harga (Rp)</label>
                    <input type="number" name="price" value="{{ old('price', $program->price ?? 0) }}" class="input-field" min="0" required>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-ink">Kategori</label>
                    <select name="category_id" class="input-field">
                        <option value="">— Pilih kategori —</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $program->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-ink">Partner / Perusahaan</label>
                    <input type="text" name="partner_name" value="{{ old('partner_name', $program->partner?->name) }}" class="input-field" placeholder="Ketik nama perusahaan" />
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-ink">Ringkasan</label>
                <textarea name="excerpt" rows="2" class="input-field">{{ old('excerpt', $program->excerpt) }}</textarea>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-ink">Deskripsi</label>
                <textarea name="description" rows="5" class="input-field">{{ old('description', $program->description) }}</textarea>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-ink">Benefits</label>
                <textarea name="benefits_text" rows="4" class="input-field">{{ old('benefits_text', collect($program->benefits ?? [])->implode("\n")) }}</textarea>
            </div>
        </section>

        <div class="flex gap-3">
            <button class="btn-primary" type="submit">Simpan bootcamp</button>
            <a href="{{ route('admin.programs.index', ['type' => 'bootcamp']) }}" class="btn-secondary">Batal</a>
        </div>
    </form>
@else
    <form method="POST"
          action="{{ $program->exists ? route('admin.programs.update', $program) : route('admin.programs.store') }}"
          class="mx-auto max-w-4xl space-y-6">
        @csrf
        @if ($program->exists)
            @method('PUT')
        @endif
        <input type="hidden" name="type" value="internship">
        <input type="hidden" name="level" value="Beginner">
        <input type="hidden" name="price" value="0">

        <div class="rounded-2xl border border-brand/20 bg-brand-mist/50 px-4 py-3 text-sm text-ink-soft">
            Lowongan magang tampil di katalog <strong class="text-ink">Magang</strong> di navbar.
        </div>

        <section class="card-soft space-y-5 p-6">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-brand-dark">Detail lowongan</p>
                <h2 class="mt-1 font-display text-lg font-semibold text-ink">Data yang tampil di katalog Magang</h2>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-ink">Role lowongan <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $program->title) }}" class="input-field" placeholder="Contoh: E-Book Development" required>
                @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-ink">Jenjang <span class="text-red-500">*</span></label>
                    <select name="education_level" class="input-field" required>
                        <option value="">— Pilih jenjang —</option>
                        @foreach (['D3', 'D4', 'S1'] as $jenjang)
                            <option value="{{ $jenjang }}" @selected(old('education_level', $program->education_level) === $jenjang)>{{ $jenjang }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-ink">Durasi (bulan) <span class="text-red-500">*</span></label>
                    <input type="number" name="duration_months" value="{{ old('duration_months', $program->duration_months ?? 3) }}" class="input-field" min="1" required>
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-ink">Prodi</label>
                <textarea name="majors" rows="2" class="input-field" placeholder="Contoh: Sastra Indonesia, Ilmu Komunikasi, Manajemen...">{{ old('majors', $program->majors) }}</textarea>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-ink">Divisi</label>
                    <input type="text" name="division" value="{{ old('division', $program->division) }}" class="input-field" placeholder="Contoh: Divisi COE">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-ink">Lokasi</label>
                    <input type="text" name="location" value="{{ old('location', $program->location) }}" class="input-field" placeholder="Contoh: PT Tiga Serangkai, Surakarta">
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-ink">Deadline pendaftaran</label>
                <input type="date" name="deadline" value="{{ old('deadline', optional($program->deadline)->format('Y-m-d')) }}" class="input-field max-w-xs">
            </div>

            <div class="rounded-2xl border border-brand/15 bg-gradient-to-br from-brand-mist/50 to-panel p-4">
                <p class="text-sm font-semibold text-ink">Sasaran pelamar</p>
                <p class="mt-0.5 text-xs text-ink-soft">Nyalakan toggle untuk Umum dan/atau TS Group. Jika keduanya menyala, lowongan tampil di kedua tempat tanpa diisi dua kali — dan jika keduanya dimatikan, lowongan tidak tampil di mana pun.</p>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <label class="flex cursor-pointer items-center justify-between gap-4 rounded-xl border border-brand/15 bg-panel px-4 py-3">
                        <span>
                            <span class="block text-sm font-semibold text-ink">Terbuka umum</span>
                            <span class="block text-xs text-ink-soft">Terlihat oleh semua pengguna.</span>
                        </span>
                        <input type="checkbox" data-audience-umum class="peer sr-only" @checked(in_array(old('audience', $program->audience ?? 'all'), ['all', 'both'], true))>
                        <span class="pointer-events-none relative inline-flex h-7 w-12 shrink-0 items-center rounded-full bg-ink/25 transition after:absolute after:left-1 after:top-1 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition-all after:content-[''] peer-checked:bg-brand peer-checked:after:translate-x-5"></span>
                    </label>
                    <label class="flex cursor-pointer items-center justify-between gap-4 rounded-xl border border-brand/15 bg-panel px-4 py-3">
                        <span>
                            <span class="block text-sm font-semibold text-ink">TS Group</span>
                            <span class="block text-xs text-ink-soft">Terlihat oleh mahasiswa TSU.</span>
                        </span>
                        <input type="checkbox" data-audience-tsu class="peer sr-only" @checked(old('audience', $program->audience ?? 'all') === 'tsu' || old('audience', $program->audience ?? 'all') === 'both')>
                        <span class="pointer-events-none relative inline-flex h-7 w-12 shrink-0 items-center rounded-full bg-ink/25 transition after:absolute after:left-1 after:top-1 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition-all after:content-[''] peer-checked:bg-brand peer-checked:after:translate-x-5"></span>
                    </label>
                </div>
                <input type="hidden" name="audience" value="{{ old('audience', $program->audience ?? 'all') }}">
                @error('audience') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="rounded-2xl border border-brand/15 bg-gradient-to-br from-brand-mist/50 to-panel p-4">
                <div class="mb-3 flex items-start gap-3">
                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand/25 text-brand-mid">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </span>
                    <div>
                        <p class="font-semibold text-ink">Kualifikasi peserta</p>
                        <p class="mt-0.5 text-xs text-ink-soft">Satu baris = satu poin checklist di kartu magang.</p>
                    </div>
                </div>
                <textarea name="qualifications_text" rows="6" class="input-field font-medium leading-relaxed"
                          placeholder="Mahasiswa aktif S1&#10;Memahami kaidah Bahasa Indonesia">{{ $qualificationsText }}</textarea>
            </div>
        </section>

        <section class="card-soft space-y-5 p-6">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-brand-dark">Halaman detail</p>
                <h2 class="mt-1 font-display text-lg font-semibold text-ink">Konten untuk Lihat Detail</h2>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-ink">Deskripsi pekerjaan</label>
                <textarea name="description" rows="5" class="input-field">{{ old('description', $program->description) }}</textarea>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-ink">Persyaratan dokumen</label>
                <textarea name="required_documents_text" rows="3" class="input-field">{{ old('required_documents_text', collect($program->required_documents ?? [])->implode("\n")) }}</textarea>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-ink">Skill yang diutamakan</label>
                <textarea name="preferred_skills_text" rows="3" class="input-field">{{ old('preferred_skills_text', collect($program->preferred_skills ?? [])->implode("\n")) }}</textarea>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-ink">Benefit selama magang</label>
                    <textarea name="benefits_text" rows="5" class="input-field">{{ old('benefits_text', collect($program->benefits ?? [])->implode("\n")) }}</textarea>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-ink">Tanggung jawab</label>
                    <textarea name="responsibilities_text" rows="5" class="input-field">{{ old('responsibilities_text', collect($program->responsibilities ?? [])->implode("\n")) }}</textarea>
                </div>
            </div>
        </section>

        <div class="flex gap-3">
            <button class="btn-primary" type="submit">{{ $program->exists ? 'Simpan perubahan' : 'Simpan lowongan magang' }}</button>
            @if ($program->exists)
                <a href="{{ route('admin.programs.publikasi', $program) }}" class="btn-secondary">Atur publikasi</a>
            @endif
            <a href="{{ route('admin.programs.index', ['type' => 'internship']) }}" class="btn-secondary">Batal</a>
        </div>
    </form>
@endif

<script>
(() => {
    document.querySelectorAll('form').forEach((form) => {
        const umum = form.querySelector('[data-audience-umum]');
        const tsu = form.querySelector('[data-audience-tsu]');
        const input = form.querySelector('input[name="audience"]');
        if (! umum || ! tsu || ! input) return;

        const sync = () => {
            if (umum.checked && tsu.checked) input.value = 'both';
            else if (tsu.checked) input.value = 'tsu';
            else if (umum.checked) input.value = 'all';
            else input.value = 'none';
        };
        umum.addEventListener('change', sync);
        tsu.addEventListener('change', sync);
    });
})();
</script>
@endsection
