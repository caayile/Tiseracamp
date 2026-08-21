@extends('layouts.app')

@section('title', 'Daftar Magang — '.$program->title)

@section('content')
<section class="mx-auto max-w-5xl px-4 py-10">
    <div class="mb-6">
        <x-back-nav :fallback="route('programs.show', $program->slug)" />
        <h2 class="mt-2 font-display text-2xl font-semibold text-ink">Daftar Magang</h2>
        <p class="mt-1 text-sm text-ink-soft">Lengkapi data diri dan unggah berkas persyaratan. Setelah dikirim, status menjadi menunggu seleksi.</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-3 lg:items-start">
        <aside class="card-soft p-6 lg:sticky lg:top-6">
            <p class="mb-4 text-xs font-semibold uppercase tracking-wide text-ink-soft">Alur pendaftaran magang</p>
            <x-vertical-stepper :steps="\App\Models\InternshipApplication::previewSteps()" />
        </aside>

        <form method="POST" action="{{ route('internships.store', $program) }}" enctype="multipart/form-data" class="card-soft space-y-5 p-6 lg:col-span-2">
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

        @php
            $hasSavedCv = filled($savedCv?->portfolio_file_url);
            $hasSavedPortfolio = filled($savedPortfolio?->portfolio_file_url) || filled($savedPortfolio?->project_url);
            $useSavedCvDefault = old('use_saved_cv', $hasSavedCv && ! $application?->cv_path);
            $useSavedPortfolioDefault = old('use_saved_portfolio', $hasSavedPortfolio && ! $application?->portfolio_path && ! $application?->portfolio_url);
            $prefillPortfolioUrl = old('portfolio_url', $application->portfolio_url ?? ($hasSavedPortfolio ? ($savedPortfolio->project_url ?? '') : ''));
            $cvUploadOptional = $application?->cv_path || $useSavedCvDefault;
        @endphp

        @if ($hasSavedCv || $hasSavedPortfolio)
            <div class="rounded-xl border border-brand/20 bg-brand-mist/40 p-4">
                <p class="text-sm font-semibold text-ink">Dari galeri karier</p>
                <p class="mt-1 text-xs text-ink-soft">Dokumen yang sudah diunggah di Galeri Portofolio bisa dipakai langsung. Cek dulu, lalu kirim.</p>
                <div class="mt-3 space-y-3">
                    @if ($hasSavedCv)
                        <label class="flex items-start gap-3 rounded-lg border border-brand/15 bg-panel px-3 py-2.5 text-sm">
                            <input type="checkbox" name="use_saved_cv" value="1" class="mt-0.5" @checked($useSavedCvDefault) data-toggle-cv>
                            <span>
                                <span class="font-medium text-ink">Gunakan CV tersimpan</span>
                                <span class="mt-0.5 block text-xs text-ink-soft">{{ $savedCv->title }}
                                    · <a href="{{ media_url($savedCv->portfolio_file_url) }}" target="_blank" class="text-brand-dark underline">Lihat</a>
                                </span>
                            </span>
                        </label>
                    @endif
                    @if ($hasSavedPortfolio)
                        <label class="flex items-start gap-3 rounded-lg border border-brand/15 bg-panel px-3 py-2.5 text-sm">
                            <input type="checkbox" name="use_saved_portfolio" value="1" class="mt-0.5" @checked($useSavedPortfolioDefault)>
                            <span>
                                <span class="font-medium text-ink">Gunakan portofolio tersimpan</span>
                                <span class="mt-0.5 block text-xs text-ink-soft">{{ $savedPortfolio->title }}
                                    @if ($savedPortfolio->project_url)
                                        · <a href="{{ $savedPortfolio->project_url }}" target="_blank" class="text-brand-dark underline">Link</a>
                                    @endif
                                    @if ($savedPortfolio->portfolio_file_url)
                                        · <a href="{{ media_url($savedPortfolio->portfolio_file_url) }}" target="_blank" class="text-brand-dark underline">PDF</a>
                                    @endif
                                </span>
                            </span>
                        </label>
                    @endif
                </div>
                @if (! $hasSavedCv)
                    <p class="mt-3 text-xs text-ink-soft">Belum punya CV di galeri? <a href="{{ route('career.gallery') }}#portfolio-upload" class="font-medium text-brand-dark underline">Unggah dulu</a>.</p>
                @endif
            </div>
        @endif

        <div class="border-t border-brand/10 pt-5">
            <p class="mb-4 text-sm font-semibold text-ink">Berkas persyaratan</p>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium">CV (PDF/DOC) @unless ($cvUploadOptional)<span class="text-red-600">*</span>@endunless</label>
                    <input type="file" name="cv" accept=".pdf,.doc,.docx" class="input-field file:mr-3 file:rounded-lg file:border-0 file:bg-brand/20 file:px-3 file:py-1.5 file:text-xs file:font-semibold" id="cv-upload" @required(! $cvUploadOptional)>
                    @if ($application?->cv_path)
                        <p class="mt-1 text-xs text-ink-soft">Sudah ada CV di pendaftaran ini. Upload ulang untuk mengganti.</p>
                    @elseif ($hasSavedCv)
                        <p class="mt-1 text-xs text-ink-soft">Centang CV tersimpan di atas, atau upload file baru di sini.</p>
                    @endif
                    @error('cv') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Link portfolio <span class="font-normal text-ink-soft">(opsional)</span></label>
                    <input type="url" name="portfolio_url" value="{{ $prefillPortfolioUrl }}" class="input-field" placeholder="https://...">
                    @error('portfolio_url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Portfolio PDF <span class="font-normal text-ink-soft">(opsional)</span></label>
                    <input type="file" name="portfolio_file" accept=".pdf" class="input-field file:mr-3 file:rounded-lg file:border-0 file:bg-brand/20 file:px-3 file:py-1.5 file:text-xs file:font-semibold">
                    @if ($application?->portfolio_path)
                        <p class="mt-1 text-xs text-ink-soft">Sudah ada portfolio PDF tersimpan. Upload ulang untuk mengganti.</p>
                    @elseif ($hasSavedPortfolio && $savedPortfolio?->portfolio_file_url)
                        <p class="mt-1 text-xs text-ink-soft">Centang portofolio tersimpan di atas, atau upload file baru.</p>
                    @endif
                    @error('portfolio_file') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <button type="submit" class="btn-primary w-full">Kirim pendaftaran</button>
    </form>
    </div>

    @if ($hasSavedCv)
    <script>
    (() => {
        const toggle = document.querySelector('[data-toggle-cv]');
        const upload = document.getElementById('cv-upload');
        if (!toggle || !upload) return;
        const sync = () => { upload.required = !toggle.checked; };
        toggle.addEventListener('change', sync);
        sync();
    })();
    </script>
    @endif
</section>
@endsection
