@extends('layouts.app')

@section('title', 'Review CV AI')

@section('content')
@php
    $journey = [
        1 => 'Review CV',
        2 => 'Analisa Karir & Skill',
        3 => 'Buat Cover Letter',
        4 => 'Latihan Interview',
    ];
@endphp

<section class="bg-surface py-8 sm:py-10">
    <div class="mx-auto max-w-6xl px-4">
        <div class="mb-6">
            <a href="{{ route('cv-review.plans') }}" class="mb-3 inline-flex items-center gap-1.5 text-sm font-semibold text-brand-mid transition hover:text-brand-deeper">
                ← Kembali ke pilihan paket
            </a>
            <p class="font-display text-sm font-bold uppercase tracking-[0.28em] text-brand-dark">Karier tools</p>
            <h1 class="mt-2 font-display text-3xl font-extrabold tracking-tight text-ink">Review CV AI</h1>
            <p class="mt-2 max-w-2xl text-sm text-ink-soft">Isi form target karier dulu, unggah CV, lalu AI kasih skor per bagian + analisa kecocokan.</p>
        </div>

        <div class="grid items-start gap-5 lg:grid-cols-[260px_minmax(0,1fr)]">
            <aside class="rounded-2xl border border-ink/10 bg-panel p-4 shadow-sm">
                <p class="px-2 text-sm font-semibold text-ink">Perjalanan Persiapan Karir</p>
                <nav class="mt-3 space-y-1">
                    @foreach ($journey as $step => $label)
                        <div class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm {{ $step === 1 ? 'bg-brand-mist font-semibold text-brand-mid' : 'text-ink-soft' }}">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full {{ $step === 1 ? 'bg-brand text-brand-navy' : 'bg-ink/10' }}">
                                <span class="text-[10px] font-bold">{{ $step }}</span>
                            </span>
                            <span>{{ $step }}. {{ $label }}</span>
                        </div>
                    @endforeach
                </nav>
            </aside>

            <div class="rounded-2xl border border-ink/10 bg-panel p-5 sm:p-7 shadow-sm">
                <h2 class="font-display text-xl font-bold text-ink">1. Review CV</h2>
                <p class="mt-1 text-sm text-ink-soft">Tuliskan data di bawah, lalu unggah CV untuk mulai analisis.</p>

                @if ($subscription)
                    <div class="mt-4 flex flex-col gap-3 rounded-xl border border-brand/20 bg-brand-mist/50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-ink-soft">
                            Paket aktif: <strong class="text-ink">{{ $subscription->plan_name }}</strong>
                            · Sisa review:
                            <strong class="text-ink">{{ $subscription->remainingReviews() === null ? 'Tanpa batas' : $subscription->remainingReviews().'x' }}</strong>
                        </p>
                        <a href="{{ route('cv-review.plans') }}" class="btn-secondary inline-flex shrink-0 items-center justify-center gap-1.5 text-xs sm:text-sm">
                            ← Kembali ke pilihan paket
                        </a>
                    </div>
                @endif

                @if ($cvReviewReady ?? false)
                    <form method="POST" action="{{ route('cv-review.store') }}" enctype="multipart/form-data" class="mt-6 space-y-5"
                          data-cv-review-form
                          data-ai-loading-form
                          data-ai-loading-label="Sedang menganalisis..."
                          data-ai-loading-hint="Proses biasanya 20–40 detik. Jangan tutup halaman.">
                        @csrf

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="mb-1.5 block text-sm font-semibold text-ink">Posisi / pekerjaan tujuan <span class="text-red-500">*</span></label>
                                <input type="text" name="target_position" value="{{ old('target_position') }}" class="input-field" placeholder="Contoh: Product Manager / Frontend Developer" required>
                                @error('target_position') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-ink">Nama perusahaan <span class="font-normal text-ink-soft">(opsional)</span></label>
                                <input type="text" name="company_name" value="{{ old('company_name') }}" class="input-field" placeholder="Contoh: PT Tiga Serangkai">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-ink">Lokasi tujuan</label>
                                <input type="text" name="location" value="{{ old('location') }}" class="input-field" placeholder="Contoh: Surakarta / Remote">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-ink">Jenjang pendidikan</label>
                                <select name="education_level" class="input-field">
                                    <option value="">— Pilih —</option>
                                    @foreach (['D3', 'D4', 'S1', 'SMA/SMK', 'Lainnya'] as $level)
                                        <option value="{{ $level }}" @selected(old('education_level') === $level)>{{ $level }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-ink">Bidang / minat</label>
                                <input type="text" name="preferred_field" value="{{ old('preferred_field') }}" class="input-field" placeholder="Contoh: Teknologi, Marketing, Desain">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-1.5 block text-sm font-semibold text-ink">Level pengalaman</label>
                                <select name="experience_level" class="input-field">
                                    <option value="">— Pilih —</option>
                                    <option value="mahasiswa" @selected(old('experience_level') === 'mahasiswa')>Mahasiswa / magang</option>
                                    <option value="fresh_graduate" @selected(old('experience_level') === 'fresh_graduate')>Fresh graduate</option>
                                    <option value="1-2_tahun" @selected(old('experience_level') === '1-2_tahun')>1–2 tahun</option>
                                    <option value="3+_tahun" @selected(old('experience_level') === '3+_tahun')>3+ tahun</option>
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-1.5 block text-sm font-semibold text-ink">Upload CV (PDF, max 5MB) <span class="text-red-500">*</span></label>
                                <input type="file" name="cv" accept="application/pdf,.pdf" required
                                       class="input-field file:mr-3 file:rounded-lg file:border-0 file:bg-brand/20 file:px-3 file:py-1.5 file:text-xs file:font-semibold">
                                @error('cv') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="btn-primary" data-cv-review-submit>Selanjutnya — Review CV</button>
                            <p class="mt-2 text-sm text-ink-soft">Proses biasanya 20–40 detik. Jangan tutup halaman.</p>
                        </div>
                    </form>
                @else
                    <div class="mt-6 rounded-2xl border border-dashed border-brand/25 bg-brand-mist/40 px-5 py-8 text-center">
                        <p class="font-display text-lg font-semibold text-ink">Segera hadir</p>
                        <p class="mt-2 text-sm text-ink-soft">Set <code class="text-xs">GEMINI_API_KEY</code> atau <code class="text-xs">GROQ_API_KEY</code> di `.env` untuk mengaktifkan.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

@include('partials.ai-loading')
@endsection
