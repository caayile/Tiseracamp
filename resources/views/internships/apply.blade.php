@extends('layouts.app')

@section('title', 'Daftar Magang — '.$program->title)

@section('content')
<section class="mx-auto max-w-3xl px-4 py-10">
    <div class="mb-6">
        <a href="{{ route('programs.show', $program->slug) }}" class="text-sm font-medium text-brand-mid hover:underline">← {{ $program->title }}</a>
        <h2 class="mt-2 font-display text-2xl font-semibold text-ink">Daftar Magang</h2>
        <p class="mt-1 text-sm text-ink-soft">Lengkapi data diri dan unggah berkas persyaratan. Setelah dikirim, status menjadi menunggu seleksi.</p>
    </div>

    <form method="POST" action="{{ route('internships.store', $program) }}" enctype="multipart/form-data" class="card-soft space-y-5 p-6">
        @csrf

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="mb-1.5 block text-sm font-medium">Nama lengkap</label>
                <input type="text" name="full_name" value="{{ old('full_name', $application->full_name ?? $user->name) }}" class="input-field" required>
                @error('full_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium">No. telepon / WhatsApp</label>
                <input type="tel" name="phone" value="{{ old('phone', $application->phone ?? $user->phone) }}" class="input-field" required>
                @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium">Jenjang</label>
                <select name="education_level" class="input-field" required>
                    <option value="" disabled @selected(! old('education_level', $application->education_level ?? $user->education_level))>Pilih jenjang</option>
                    @foreach (['D3', 'D4', 'S1'] as $level)
                        <option value="{{ $level }}" @selected(old('education_level', $application->education_level ?? $user->education_level) === $level)>{{ $level }}</option>
                    @endforeach
                </select>
                @error('education_level') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium">Semester / tingkat</label>
                <input type="text" name="semester" value="{{ old('semester', $application->semester ?? $user->semester) }}" class="input-field" placeholder="Contoh: Semester 6" required>
                @error('semester') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium">Universitas / sekolah</label>
                <input type="text" name="university" value="{{ old('university', $application->university ?? $user->university) }}" class="input-field" required>
                @error('university') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium">Jurusan</label>
                <input type="text" name="major" value="{{ old('major', $application->major ?? $user->major) }}" class="input-field" required>
                @error('major') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="border-t border-brand/10 pt-5">
            <p class="mb-4 text-sm font-semibold text-ink">Berkas persyaratan</p>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium">CV (PDF/DOC) *</label>
                    <input type="file" name="cv" accept=".pdf,.doc,.docx" class="input-field file:mr-3 file:rounded-lg file:border-0 file:bg-brand/20 file:px-3 file:py-1.5 file:text-xs file:font-semibold" @required(! ($application?->cv_path))>
                    @if ($application?->cv_path)
                        <p class="mt-1 text-xs text-ink-soft">Sudah ada CV tersimpan. Upload ulang untuk mengganti.</p>
                    @endif
                    @error('cv') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Transkrip nilai</label>
                    <input type="file" name="transcript" accept=".pdf,.jpg,.jpeg,.png" class="input-field file:mr-3 file:rounded-lg file:border-0 file:bg-brand/20 file:px-3 file:py-1.5 file:text-xs file:font-semibold" @required(! ($application?->transcript_path))>
                    @if ($application?->transcript_path)
                        <p class="mt-1 text-xs text-ink-soft">Sudah ada transkrip tersimpan. Upload ulang untuk mengganti.</p>
                    @endif
                    @error('transcript') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Surat pengantar magang *</label>
                    <input type="file" name="cover_letter" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="input-field file:mr-3 file:rounded-lg file:border-0 file:bg-brand/20 file:px-3 file:py-1.5 file:text-xs file:font-semibold" @required(! ($application?->cover_letter_path))>
                    @if ($application?->cover_letter_path)
                        <p class="mt-1 text-xs text-ink-soft">Sudah ada surat tersimpan. Upload ulang untuk mengganti.</p>
                    @endif
                    @error('cover_letter') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Link portfolio</label>
                    <input type="url" name="portfolio_url" value="{{ old('portfolio_url', $application->portfolio_url ?? '') }}" class="input-field" placeholder="https://...">
                    @error('portfolio_url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <button type="submit" class="btn-primary w-full">Kirim pendaftaran</button>
    </form>
</section>
@endsection
