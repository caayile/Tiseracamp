@extends('layouts.mentor')

@section('title', $program->exists ? 'Edit Magang' : 'Tambah Magang')
@section('heading', $program->exists ? 'Edit Magang' : 'Tambah Magang')

@section('content')
@php
    $quotaValue = old('quota', $program->internshipQuota() ?? 20);
    $filled = $program->exists ? $program->acceptedInternCount() : 0;
@endphp

<div class="mb-6">
    <a href="{{ route('mentor.internships.index') }}" class="btn-secondary">← Magang saya</a>
</div>

<form method="POST"
      action="{{ $program->exists ? route('mentor.internships.update', $program) : route('mentor.internships.store') }}"
      class="mx-auto max-w-4xl space-y-6">
    @csrf
    @if ($program->exists)
        @method('PUT')
    @endif

    <div class="rounded-2xl border border-brand/20 bg-brand-mist/50 px-4 py-3 text-sm text-ink-soft">
        Magang langsung aktif di katalog — <strong class="text-ink">tanpa approve admin</strong>. Setelah simpan, kamu diarahkan isi materi Minggu 1–4; peserta melihatnya di ruang belajar.
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
                @error('education_level') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
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

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-ink">Deadline pendaftaran</label>
                <input type="date" name="deadline" value="{{ old('deadline', optional($program->deadline)->format('Y-m-d')) }}" class="input-field">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-ink">Kuota peserta <span class="text-red-500">*</span></label>
                <input type="number" name="quota" value="{{ $quotaValue }}" class="input-field" min="{{ max(1, $filled) }}" max="500" required>
                <p class="mt-1 text-xs text-ink-soft">
                    Jumlah kursi yang bisa diterima.
                    @if ($filled > 0)
                        Sudah terisi {{ $filled }} peserta.
                    @endif
                </p>
                @error('quota') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-semibold text-ink">Kualifikasi peserta</label>
            <textarea name="qualifications_text" rows="5" class="input-field" placeholder="Mahasiswa aktif S1&#10;Memahami kaidah Bahasa Indonesia">{{ old('qualifications_text', collect($program->qualifications ?? [])->implode("\n")) }}</textarea>
            <p class="mt-1 text-xs text-ink-soft">Satu baris = satu poin.</p>
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

    <div class="flex flex-wrap gap-3">
        <button class="btn-primary" type="submit">{{ $program->exists ? 'Simpan perubahan' : 'Simpan magang' }}</button>
        <a href="{{ route('mentor.internships.index') }}" class="btn-secondary">Batal</a>
    </div>
</form>
@endsection
