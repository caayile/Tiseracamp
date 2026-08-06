@extends('layouts.app')

@section('title', 'Hasil Review CV AI')

@section('content')
@php
    $score = (int) ($result['score'] ?? $review->score ?? 0);
    $points = collect($points ?? []);
    $activePoint = $points->firstWhere('id', $activePointId) ?? $points->first();
    $journeyStep = (int) ($journeyStep ?? 1);

    $scoreBadge = function (int $value): string {
        if ($value >= 80) {
            return 'bg-emerald-100 text-emerald-800 ring-emerald-200';
        }
        if ($value >= 60) {
            return 'bg-amber-100 text-amber-800 ring-amber-200';
        }

        return 'bg-rose-100 text-rose-800 ring-rose-200';
    };

    $journey = [
        1 => 'Review CV',
        2 => 'Analisa Karir & Skill',
        3 => 'Buat Cover Letter',
        4 => 'Latihan Interview',
    ];
@endphp

<section class="bg-surface py-8 sm:py-10">
    <div class="mx-auto max-w-6xl px-4">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <x-back-nav :fallback="route('cv-review.index')" />
            <a href="{{ route('cv-review.index') }}" class="btn-secondary text-sm">Review CV lain</a>
        </div>

        <div class="grid items-start gap-5 lg:grid-cols-[260px_minmax(0,1fr)]">
            {{-- Sidebar perjalanan --}}
            <aside class="rounded-2xl border border-ink/10 bg-panel p-4 shadow-sm">
                <p class="px-2 text-sm font-semibold text-ink">Perjalanan Persiapan Karir</p>
                <nav class="mt-3 space-y-1">
                    @foreach ($journey as $step => $label)
                        @php
                            $isActive = $journeyStep === $step;
                            $isDone = $step < $journeyStep || ($step === 1 && $journeyStep >= 1);
                            $href = route('cv-review.show', ['cvReview' => $review, 'step' => $step]);
                        @endphp
                        <a href="{{ $href }}"
                           class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition {{ $isActive ? 'bg-brand-mist font-semibold text-brand-mid' : 'text-ink-soft hover:bg-brand-mist/50 hover:text-ink' }}">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full {{ $isActive || $isDone ? 'bg-brand text-brand-navy' : 'bg-ink/10 text-ink-soft' }}">
                                @if ($isDone && ! $isActive)
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                @elseif ($isActive)
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                @else
                                    <span class="text-[10px] font-bold">{{ $step }}</span>
                                @endif
                            </span>
                            <span>{{ $step }}. {{ $label }}</span>
                        </a>
                    @endforeach
                </nav>

                @if ($journeyStep === 1 && $points->isNotEmpty())
                    <div class="mt-5 border-t border-ink/8 pt-4">
                        <p class="px-2 text-xs font-bold uppercase tracking-wide text-ink-soft">Bagian CV</p>
                        <div class="mt-2 space-y-1">
                            @foreach ($points as $point)
                                <a href="{{ route('cv-review.show', ['cvReview' => $review, 'point' => $point['id']]) }}"
                                   class="flex items-center justify-between gap-2 rounded-xl px-3 py-2 text-xs transition {{ ($activePoint['id'] ?? null) === $point['id'] ? 'bg-brand/15 font-semibold text-ink' : 'text-ink-soft hover:bg-brand-mist' }}">
                                    <span class="line-clamp-1">{{ $point['label'] }}</span>
                                    <span class="shrink-0 rounded-md px-1.5 py-0.5 text-[10px] font-bold ring-1 {{ $scoreBadge((int) $point['score']) }}">{{ (int) $point['score'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </aside>

            {{-- Konten utama --}}
            <div class="min-w-0 space-y-5">
                @if ($journeyStep === 1)
                    <div class="rounded-2xl border border-ink/10 bg-panel p-5 sm:p-6">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.16em] text-brand-mid">1. Review CV</p>
                                <h1 class="mt-1 font-display text-2xl font-bold text-ink">Hasil analisis CV</h1>
                                @if ($review->original_filename)
                                    <p class="mt-1 text-sm text-ink-soft">{{ $review->original_filename }}</p>
                                @endif
                            </div>
                            <div class="rounded-2xl bg-brand-mist px-5 py-3 text-center">
                                <p class="text-[10px] font-bold uppercase tracking-wide text-brand-mid">Skor keseluruhan</p>
                                <p class="font-display text-4xl font-extrabold text-ink">{{ $score }}<span class="text-base font-semibold text-ink-soft">/100</span></p>
                            </div>
                        </div>
                        @if (! empty($result['summary']))
                            <p class="mt-4 text-sm leading-relaxed text-ink-soft">{{ $result['summary'] }}</p>
                        @endif

                        @if (! empty($result['strengths']) || ! empty($result['weaknesses']))
                            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                <div class="rounded-xl border border-emerald-100 bg-emerald-50/70 p-4">
                                    <p class="text-sm font-semibold text-emerald-800">Kekuatan</p>
                                    <ul class="mt-2 space-y-1.5 text-sm text-ink-soft">
                                        @foreach ($result['strengths'] ?? [] as $item)
                                            <li>• {{ $item }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="rounded-xl border border-amber-100 bg-amber-50/70 p-4">
                                    <p class="text-sm font-semibold text-amber-800">Perlu diperbaiki</p>
                                    <ul class="mt-2 space-y-1.5 text-sm text-ink-soft">
                                        @foreach ($result['weaknesses'] ?? [] as $item)
                                            <li>• {{ $item }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if ($activePoint)
                        @php $pointScore = (int) ($activePoint['score'] ?? 0); @endphp
                        <article class="rounded-2xl border border-ink/10 bg-panel p-5 sm:p-7">
                            <div class="flex flex-wrap items-center gap-3">
                                <h2 class="font-display text-xl font-bold text-ink">{{ $activePoint['label'] }}</h2>
                                <span class="rounded-lg px-2.5 py-1 text-xs font-bold ring-1 {{ $scoreBadge($pointScore) }}">
                                    Skor: {{ $pointScore }}/100
                                </span>
                            </div>

                            <div class="mt-6">
                                <h3 class="font-semibold text-ink">Analisa dari CV-mu</h3>
                                <p class="mt-2 text-sm leading-relaxed text-ink-soft">{{ $activePoint['analysis'] ?: 'Analisa belum tersedia untuk bagian ini.' }}</p>
                            </div>

                            @if (! empty($activePoint['suggestions']))
                                <div class="mt-7">
                                    <h3 class="font-semibold text-ink">Saran Perbaikan</h3>
                                    <ul class="mt-3 space-y-5">
                                        @foreach ($activePoint['suggestions'] as $suggestion)
                                            <li class="rounded-xl border border-ink/8 bg-surface/60 p-4">
                                                <p class="font-semibold text-ink">{{ $suggestion['title'] }}</p>
                                                @if (! empty($suggestion['detail']))
                                                    <p class="mt-1.5 text-sm leading-relaxed text-ink-soft">{{ $suggestion['detail'] }}</p>
                                                @endif
                                                @if (! empty($suggestion['current']) || ! empty($suggestion['improved']))
                                                    <div class="mt-3 space-y-2 text-sm">
                                                        @if (! empty($suggestion['current']))
                                                            <p><span class="font-semibold text-ink">Contoh Saat Ini:</span> <span class="text-ink-soft">{{ $suggestion['current'] }}</span></p>
                                                        @endif
                                                        @if (! empty($suggestion['improved']))
                                                            <p><span class="font-semibold text-ink">Contoh Saran Perbaikan:</span> <span class="text-ink-soft">{{ $suggestion['improved'] }}</span></p>
                                                        @endif
                                                    </div>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if (! empty($activePoint['hr_criteria']))
                                <div class="mt-7">
                                    <h3 class="font-semibold text-ink">Yang Dinilai oleh HR</h3>
                                    <ul class="mt-3 space-y-3">
                                        @foreach ($activePoint['hr_criteria'] as $criterion)
                                            <li class="text-sm leading-relaxed text-ink-soft">
                                                <span class="font-semibold text-ink">{{ $criterion['title'] }}.</span>
                                                {{ $criterion['description'] }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @php
                                $pointIds = $points->pluck('id')->values();
                                $currentIndex = $pointIds->search($activePoint['id']);
                                $prevId = $currentIndex > 0 ? $pointIds[$currentIndex - 1] : null;
                                $nextId = ($currentIndex !== false && $currentIndex < $pointIds->count() - 1) ? $pointIds[$currentIndex + 1] : null;
                            @endphp
                            <div class="mt-8 flex flex-wrap gap-3">
                                @if ($prevId)
                                    <a href="{{ route('cv-review.show', ['cvReview' => $review, 'point' => $prevId]) }}" class="btn-secondary">Kembali</a>
                                @endif
                                @if ($nextId)
                                    <a href="{{ route('cv-review.show', ['cvReview' => $review, 'point' => $nextId]) }}" class="btn-primary">Selanjutnya</a>
                                @else
                                    <a href="{{ route('cv-review.show', ['cvReview' => $review, 'step' => 2]) }}" class="btn-primary">Lanjut Analisa Karir</a>
                                @endif
                            </div>
                        </article>
                    @else
                        <div class="rounded-2xl border border-dashed border-brand/25 bg-panel p-8 text-center text-sm text-ink-soft">
                            Belum ada breakdown per bagian. Coba review ulang CV-mu.
                        </div>
                    @endif
                @elseif ($journeyStep === 2)
                    @php
                        $career = $career ?? [];
                        $matchScore = (int) ($career['match_score'] ?? 0);
                        $jobFit = $career['job_fit'] ?? [];
                        $skillFit = $career['skill_fit'] ?? [];
                        $experienceFit = $career['experience_fit'] ?? [];
                        $statusClass = [
                            'sudah menguasai' => 'bg-emerald-100 text-emerald-800',
                            'belum menguasai' => 'bg-amber-100 text-amber-800',
                            'tidak memiliki' => 'bg-ink/10 text-ink-soft',
                        ];
                    @endphp

                    <div class="space-y-5">
                        <div class="rounded-2xl border border-ink/10 bg-panel p-5 sm:p-7">
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-brand-mid">2. Analisa Karir & Skill</p>
                            <h1 class="mt-1 font-display text-2xl font-bold text-ink">Kecocokan dengan target karier</h1>
                            <p class="mt-2 text-sm text-ink-soft">
                                Target: <span class="font-semibold text-ink">{{ $review->target_position ?: '—' }}</span>
                                @if ($review->company_name)
                                    · {{ $review->company_name }}
                                @endif
                            </p>
                            <p class="mt-3 text-xs text-ink-soft">Catatan: Meskipun sekiranya kamu memenuhi kriteria, HRD mungkin punya kandidat lain yang lebih berpengalaman. Jangan menyerah ya!</p>

                            <div class="mt-5 rounded-2xl border border-brand/15 bg-brand-mist/50 p-4 sm:p-5">
                                <p class="text-xs font-semibold uppercase tracking-wide text-brand-mid">Pekerjaan yang disarankan</p>
                                <div class="mt-2 flex flex-wrap items-center gap-3">
                                    <h2 class="font-display text-xl font-bold text-ink">{{ $career['suggested_role'] ?: ($review->target_position ?: 'Belum tersedia') }}</h2>
                                    <span class="rounded-lg bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-800 ring-1 ring-amber-200">Skor Kesesuaian : {{ $matchScore }}/100</span>
                                </div>
                                @if (! empty($career['alternatives']))
                                    <p class="mt-3 text-sm text-ink-soft">Rekomendasi pekerjaan lainnya:</p>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach ($career['alternatives'] as $alt)
                                            <span class="rounded-full border border-brand/30 px-3 py-1 text-xs font-semibold text-brand-mid">{{ $alt }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="mt-5 flex flex-wrap gap-2">
                                @foreach (['karir' => 'Karir', 'skill' => 'Analisa Skill', 'pengalaman' => 'Analisa Pengalaman'] as $tabKey => $tabLabel)
                                    <a href="{{ route('cv-review.show', ['cvReview' => $review, 'step' => 2, 'tab' => $tabKey]) }}"
                                       class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $careerTab === $tabKey ? 'bg-brand text-brand-navy' : 'border border-brand/30 text-brand-mid hover:bg-brand-mist' }}">
                                        {{ $tabLabel }}
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        @if ($careerTab === 'karir')
                            <article class="rounded-2xl border border-ink/10 bg-panel p-5 sm:p-7">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h3 class="font-display text-lg font-bold text-ink">Deskripsi Pekerjaan</h3>
                                    <span class="rounded-lg px-2.5 py-1 text-xs font-bold ring-1 {{ $scoreBadge((int) ($jobFit['score'] ?? 0)) }}">Skor : {{ (int) ($jobFit['score'] ?? 0) }}/100</span>
                                </div>
                                <h4 class="mt-5 font-semibold text-ink">Analisa dari CV-mu</h4>
                                <p class="mt-2 text-sm leading-relaxed text-ink-soft">{{ $jobFit['analysis'] ?: 'Belum ada analisa.' }}</p>
                                <ul class="mt-5 space-y-4">
                                    @forelse ($jobFit['criteria'] ?? [] as $criterion)
                                        <li>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <p class="font-semibold text-ink">{{ $criterion['title'] }}</p>
                                                <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $statusClass[$criterion['status']] ?? $statusClass['belum menguasai'] }}">{{ $criterion['status'] }}</span>
                                            </div>
                                            <p class="mt-1 text-sm leading-relaxed text-ink-soft">{{ $criterion['description'] }}</p>
                                        </li>
                                    @empty
                                        <li class="text-sm text-ink-soft">Belum ada kriteria.</li>
                                    @endforelse
                                </ul>
                            </article>
                        @elseif ($careerTab === 'skill')
                            <article class="rounded-2xl border border-ink/10 bg-panel p-5 sm:p-7">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h3 class="font-display text-lg font-bold text-ink">Kondisi Skill dengan Karir Tujuan</h3>
                                    <span class="rounded-lg px-2.5 py-1 text-xs font-bold ring-1 {{ $scoreBadge((int) ($skillFit['score'] ?? 0)) }}">Skor : {{ (int) ($skillFit['score'] ?? 0) }}/100</span>
                                </div>
                                <h4 class="mt-5 font-semibold text-ink">Analisa dari CV-mu</h4>
                                <p class="mt-2 text-sm leading-relaxed text-ink-soft">{{ $skillFit['analysis'] ?: 'Belum ada analisa.' }}</p>
                                @if (! empty($skillFit['ideal']))
                                    <h4 class="mt-5 font-semibold text-ink">Kondisi Ideal Kandidatnya</h4>
                                    <p class="mt-2 text-sm leading-relaxed text-ink-soft">{{ $skillFit['ideal'] }}</p>
                                @endif
                                @if (! empty($skillFit['requirements']))
                                    <h4 class="mt-5 font-semibold text-ink">Persyaratan Utama</h4>
                                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-ink-soft">
                                        @foreach ($skillFit['requirements'] as $req)
                                            <li>{{ $req }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                                @if (! empty($skillFit['tools']))
                                    <h4 class="mt-5 font-semibold text-ink">Tools yang Diperlukan</h4>
                                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-ink-soft">
                                        @foreach ($skillFit['tools'] as $tool)
                                            <li>{{ $tool }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                                @if (! empty($skillFit['gaps']))
                                    <h4 class="mt-5 font-semibold text-ink">Skill Gap yang Perlu Ditangani</h4>
                                    <ul class="mt-3 space-y-3">
                                        @foreach ($skillFit['gaps'] as $gap)
                                            <li>
                                                <p class="font-semibold text-ink">{{ $gap['title'] }}</p>
                                                <p class="mt-1 text-sm text-ink-soft">{{ $gap['detail'] }}</p>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </article>
                        @else
                            <article class="rounded-2xl border border-ink/10 bg-panel p-5 sm:p-7">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h3 class="font-display text-lg font-bold text-ink">Keterkaitan dengan Karir Tujuan</h3>
                                    <span class="rounded-lg px-2.5 py-1 text-xs font-bold ring-1 {{ $scoreBadge((int) ($experienceFit['score'] ?? 0)) }}">Skor : {{ (int) ($experienceFit['score'] ?? 0) }}/100</span>
                                </div>
                                <h4 class="mt-5 font-semibold text-ink">Analisa dari CV-mu</h4>
                                <p class="mt-2 text-sm leading-relaxed text-ink-soft">{{ $experienceFit['analysis'] ?: 'Belum ada analisa.' }}</p>
                                @if (! empty($experienceFit['ideal_conditions']))
                                    <h4 class="mt-5 font-semibold text-ink">Kondisi Ideal Kandidatnya</h4>
                                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-ink-soft">
                                        @foreach ($experienceFit['ideal_conditions'] as $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                                @if (! empty($experienceFit['suggestions']))
                                    <h4 class="mt-5 font-semibold text-ink">Saran Perbaikan</h4>
                                    <ul class="mt-3 space-y-3">
                                        @foreach ($experienceFit['suggestions'] as $suggestion)
                                            <li>
                                                <p class="font-semibold text-ink">{{ $suggestion['title'] }}</p>
                                                <p class="mt-1 text-sm text-ink-soft">{{ $suggestion['detail'] }}</p>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </article>
                        @endif

                        @if (! empty($jobBoards))
                            <article class="rounded-2xl border border-ink/10 bg-panel p-5 sm:p-7">
                                <p class="text-xs font-bold uppercase tracking-[0.16em] text-brand-mid">Rekomendasi lowongan</p>
                                <h3 class="mt-1 font-display text-lg font-bold text-ink">Cari lowongan yang cocok</h3>
                                <p class="mt-1 text-sm text-ink-soft">
                                    Berdasarkan posisi yang disarankan AI. Klik untuk membuka pencarian di LinkedIn, Glints, atau Jobstreet.
                                </p>

                                <div class="mt-5 space-y-4">
                                    @foreach ($jobBoards as $item)
                                        <div class="rounded-xl border border-brand/15 bg-brand-mist/40 p-4">
                                            <p class="text-xs font-semibold uppercase tracking-wide text-ink-soft">Posisi</p>
                                            <p class="mt-1 font-display text-base font-bold text-ink">{{ $item['role'] }}</p>
                                            <div class="mt-3 flex flex-wrap gap-2">
                                                @foreach ($item['boards'] as $board)
                                                    <a href="{{ $board['url'] }}"
                                                       target="_blank"
                                                       rel="noopener noreferrer"
                                                       class="inline-flex items-center gap-2 rounded-xl px-3.5 py-2 text-xs font-bold transition {{ $board['color'] }}">
                                                        {{ $board['label'] }}
                                                        <span aria-hidden="true">↗</span>
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </article>
                        @endif

                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('cv-review.show', $review) }}" class="btn-secondary">Kembali</a>
                            <a href="{{ route('cv-review.show', ['cvReview' => $review, 'step' => 3]) }}" class="btn-primary">Selanjutnya</a>
                        </div>
                    </div>
                @elseif ($journeyStep === 3)
                    @php
                        $coverLetter = $coverLetter ?? $review->cover_letter;
                    @endphp
                    <div class="rounded-2xl border border-ink/10 bg-panel p-6 sm:p-8">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-brand-mid">3. Buat Cover Letter</p>
                        <h1 class="mt-2 font-display text-2xl font-bold text-ink">Buat Cover Letter</h1>
                        <p class="mt-2 text-sm text-ink-soft">AI menulis surat lamaran berdasarkan CV & posisi tujuanmu.</p>

                        <div class="mt-6 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-xl border border-ink/10 bg-surface/70 px-4 py-3">
                                <p class="text-[11px] font-bold uppercase tracking-wide text-ink-soft">Posisi / pekerjaan tujuan</p>
                                <p class="mt-1 font-semibold text-ink">{{ $review->target_position ?: '—' }}</p>
                            </div>
                            <div class="rounded-xl border border-ink/10 bg-surface/70 px-4 py-3">
                                <p class="text-[11px] font-bold uppercase tracking-wide text-ink-soft">Nama perusahaan</p>
                                <p class="mt-1 font-semibold text-ink">{{ $review->company_name ?: '—' }}</p>
                            </div>
                        </div>

                        @if (! ($cvReviewReady ?? false))
                            <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                                Fitur AI belum dikonfigurasi. Admin perlu set <code class="text-xs">GEMINI_API_KEY</code>.
                            </div>
                        @else
                            <form method="POST" action="{{ route('cv-review.cover-letter', $review) }}"
                                  class="mt-6 space-y-4 rounded-xl border border-ink/10 bg-surface/50 p-4"
                                  data-ai-loading-form
                                  data-ai-loading-label="Sedang menulis cover letter..."
                                  data-ai-loading-hint="Proses biasanya 15–30 detik. Jangan tutup halaman.">
                                @csrf
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold text-ink">Tone surat</label>
                                        <select name="tone" class="input-field text-sm">
                                            <option value="profesional" @selected(old('tone', $coverLetter['tone'] ?? 'profesional') === 'profesional')>Profesional</option>
                                            <option value="hangat" @selected(old('tone', $coverLetter['tone'] ?? '') === 'hangat')>Hangat & personal</option>
                                            <option value="formal" @selected(old('tone', $coverLetter['tone'] ?? '') === 'formal')>Sangat formal</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold text-ink">Perusahaan (opsional ubah)</label>
                                        <input type="text" name="company_name" value="{{ old('company_name', $review->company_name) }}" class="input-field text-sm" placeholder="Nama perusahaan">
                                    </div>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-ink">Highlight tambahan (opsional)</label>
                                    <textarea name="highlights" rows="2" class="input-field text-sm" placeholder="Contoh: magang di startup fintech, juara hackathon, IPK 3.7">{{ old('highlights') }}</textarea>
                                </div>
                                <button type="submit" class="btn-primary">
                                    {{ $coverLetter ? 'Generate ulang cover letter' : 'Generate cover letter AI' }}
                                </button>
                            </form>
                        @endif

                        @if ($coverLetter)
                            <div class="mt-6 space-y-4">
                                @if (! empty($coverLetter['subject']))
                                    <div class="rounded-xl border border-ink/10 bg-white px-4 py-3">
                                        <p class="text-[11px] font-bold uppercase tracking-wide text-ink-soft">Subjek email</p>
                                        <p class="mt-1 font-semibold text-ink">{{ $coverLetter['subject'] }}</p>
                                    </div>
                                @endif
                                <div class="rounded-xl border border-ink/10 bg-white p-5">
                                    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                                        <p class="text-[11px] font-bold uppercase tracking-wide text-ink-soft">Isi surat</p>
                                        <button type="button" class="btn-ghost text-xs" onclick="navigator.clipboard.writeText(document.getElementById('cover-letter-body').innerText)">Salin teks</button>
                                    </div>
                                    <div id="cover-letter-body" class="whitespace-pre-wrap text-sm leading-relaxed text-ink">{{ $coverLetter['body'] ?? '' }}</div>
                                </div>
                                @if (! empty($coverLetter['tips']))
                                    <div class="rounded-xl border border-brand/15 bg-brand-mist/40 px-4 py-3">
                                        <p class="text-xs font-bold uppercase tracking-wide text-brand-deeper">Tips kirim</p>
                                        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-ink">
                                            @foreach ($coverLetter['tips'] as $tip)
                                                <li>{{ $tip }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <div class="mt-6 flex flex-wrap gap-3">
                            <a href="{{ route('cv-review.show', ['cvReview' => $review, 'step' => 2]) }}" class="btn-secondary">Kembali</a>
                            <a href="{{ route('cv-review.show', ['cvReview' => $review, 'step' => 4]) }}" class="btn-primary">Lanjut Latihan Interview</a>
                        </div>
                    </div>
                @else
                    @php
                        $interview = $interview ?? $review->interview;
                        $questions = $interview['questions'] ?? [];
                        $currentIndex = (int) ($interview['current_index'] ?? 0);
                        $current = $questions[$currentIndex] ?? ($questions[0] ?? null);
                        $answeredCount = (int) ($interview['answered_count'] ?? collect($questions)->filter(fn ($q) => filled($q['answer'] ?? null))->count());
                        $isComplete = $answeredCount > 0 && $answeredCount >= count($questions) && count($questions) > 0;
                    @endphp
                    <div class="rounded-2xl border border-ink/10 bg-panel p-6 sm:p-8">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-brand-mid">4. Latihan Interview</p>
                        <h1 class="mt-2 font-display text-2xl font-bold text-ink">Latihan Interview</h1>
                        <p class="mt-2 text-sm text-ink-soft">
                            Simulasi interview untuk posisi
                            <span class="font-semibold text-ink">{{ $review->target_position ?: 'target kariermu' }}</span>.
                        </p>

                        @if (! ($cvReviewReady ?? false))
                            <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                                Fitur AI belum dikonfigurasi. Admin perlu set <code class="text-xs">GEMINI_API_KEY</code>.
                            </div>
                        @elseif (empty($questions))
                            <form method="POST" action="{{ route('cv-review.interview', $review) }}" class="mt-6"
                                  data-ai-loading-form
                                  data-ai-loading-label="Sedang menyiapkan soal interview..."
                                  data-ai-loading-hint="Proses biasanya 10–25 detik. Jangan tutup halaman.">
                                @csrf
                                <button type="submit" class="btn-primary">Mulai: generate 5 pertanyaan AI</button>
                            </form>
                        @else
                            <div class="mt-4 flex flex-wrap items-center gap-3 text-sm">
                                <span class="badge">{{ $answeredCount }}/{{ count($questions) }} terjawab</span>
                                @if (! empty($interview['average_score']))
                                    <span class="badge">Rata-rata skor {{ $interview['average_score'] }}</span>
                                @endif
                                <form method="POST" action="{{ route('cv-review.interview', $review) }}"
                                      onsubmit="return confirm('Generate ulang akan menghapus jawaban sebelumnya. Lanjut?')"
                                      data-ai-loading-form
                                      data-ai-loading-label="Sedang menyiapkan soal interview..."
                                      data-ai-loading-hint="Proses biasanya 10–25 detik. Jangan tutup halaman.">
                                    @csrf
                                    <button type="submit" class="btn-ghost text-xs">Generate ulang soal</button>
                                </form>
                            </div>

                            <div class="mt-5 flex flex-wrap gap-2">
                                @foreach ($questions as $i => $q)
                                    @php
                                        $done = filled($q['answer'] ?? null);
                                        $active = $i === $currentIndex;
                                    @endphp
                                    <a href="{{ route('cv-review.show', ['cvReview' => $review, 'step' => 4, 'q' => $i]) }}"
                                       class="rounded-lg px-3 py-1.5 text-xs font-semibold {{ $active ? 'bg-brand text-white' : ($done ? 'bg-emerald-100 text-emerald-800' : 'bg-surface text-ink-soft') }}">
                                        Q{{ $i + 1 }}{{ $done ? ' ✓' : '' }}
                                    </a>
                                @endforeach
                            </div>

                            @if ($current)
                                <div class="mt-6 rounded-xl border border-ink/10 bg-white p-5">
                                    <p class="text-[11px] font-bold uppercase tracking-wide text-brand-mid">{{ $current['focus'] ?? 'umum' }}</p>
                                    <h2 class="mt-2 font-display text-lg font-bold text-ink">{{ $current['question'] }}</h2>
                                    @if (! empty($current['tip']))
                                        <p class="mt-2 text-sm text-ink-soft">Tips: {{ $current['tip'] }}</p>
                                    @endif

                                    @if (filled($current['answer'] ?? null))
                                        <div class="mt-4 space-y-3">
                                            <div class="rounded-lg bg-surface/80 px-4 py-3 text-sm text-ink">
                                                <p class="text-[11px] font-bold uppercase text-ink-soft">Jawabanmu</p>
                                                <p class="mt-1 whitespace-pre-wrap">{{ $current['answer'] }}</p>
                                            </div>
                                            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                                                <p class="font-semibold">Skor {{ $current['score'] ?? '—' }}/100</p>
                                                <p class="mt-1">{{ $current['feedback'] ?? '' }}</p>
                                            </div>
                                            @if (! empty($current['improved_answer']))
                                                <div class="rounded-lg border border-brand/15 bg-brand-mist/30 px-4 py-3 text-sm text-ink">
                                                    <p class="text-[11px] font-bold uppercase text-brand-deeper">Contoh jawaban lebih baik</p>
                                                    <p class="mt-1 whitespace-pre-wrap">{{ $current['improved_answer'] }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <form method="POST" action="{{ route('cv-review.interview.answer', $review) }}" class="mt-4 space-y-3"
                                              data-ai-loading-form
                                              data-ai-loading-label="Sedang menilai jawaban..."
                                              data-ai-loading-hint="Proses biasanya 10–20 detik. Jangan tutup halaman.">
                                            @csrf
                                            <input type="hidden" name="question_id" value="{{ $current['id'] }}">
                                            <textarea name="answer" rows="5" class="input-field text-sm" required minlength="20" placeholder="Tulis jawabanmu di sini (minimal 20 karakter)...">{{ old('answer') }}</textarea>
                                            <button type="submit" class="btn-primary">Kirim & nilai jawaban</button>
                                        </form>
                                    @endif
                                </div>
                            @endif

                            @if ($isComplete)
                                <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm text-emerald-900">
                                    <p class="font-semibold">Latihan selesai — rata-rata skor {{ $interview['average_score'] ?? '—' }}/100</p>
                                    <p class="mt-1">Ulangi soal yang skornya rendah, atau review CV baru untuk posisi lain.</p>
                                </div>
                            @endif
                        @endif

                        <div class="mt-6 flex flex-wrap gap-3">
                            <a href="{{ route('cv-review.show', ['cvReview' => $review, 'step' => 3]) }}" class="btn-secondary">Kembali</a>
                            <a href="{{ route('cv-review.index') }}" class="btn-primary">Review CV baru</a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

@include('partials.ai-loading')
@endsection
