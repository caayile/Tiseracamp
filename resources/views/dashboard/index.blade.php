@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<section class="mesh-bg border-b border-brand/10">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <x-back-nav :fallback="route('home')" class="mb-4" />
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-brand-dark">Dashboard siswa</p>
        <h1 class="section-title mt-2">Halo, {{ auth()->user()->name }}</h1>
        <p class="mt-2 text-ink-soft">Pantau progress bootcamp & magang kamu di satu tempat.</p>

        <div class="mt-6 flex flex-wrap gap-2">
            <a href="{{ route('career.index') }}" class="btn-secondary">Karier</a>
            <a href="{{ route('chat.index') }}" class="btn-secondary">Chat</a>
            <a href="{{ route('schedules.index') }}" class="btn-secondary">Jadwal</a>
            <a href="{{ route('payments.index') }}" class="btn-secondary">Pembayaran</a>
            <a href="{{ route('profile.edit') }}" class="btn-primary">Profil</a>
        </div>
    </div>
</section>

<section class="mx-auto max-w-6xl px-4 py-10">
    <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
        <div class="space-y-6">
            <div>
                <h2 class="font-display text-xl font-semibold">Program aktif</h2>
                @if ($enrollments->isEmpty())
                    <div class="card-soft mt-4 p-10 text-center">
                        <p class="font-display text-xl font-semibold">Belum ada program aktif</p>
                        <p class="mt-2 text-sm text-ink-soft">Jelajahi katalog dan daftar bootcamp atau magang favoritmu.</p>
                        <a href="{{ route('programs.index') }}" class="btn-primary mt-6">Lihat program</a>
                    </div>
                @else
                    <div class="mt-4 grid gap-4">
                        @foreach ($enrollments as $enrollment)
                            <div class="card-soft reveal p-6">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <span class="badge">{{ $enrollment->program->typeLabel() }}</span>
                                        <h3 class="mt-3 font-display text-xl font-semibold">{{ $enrollment->program->title }}</h3>
                                        <p class="mt-1 text-sm text-ink-soft">Status: {{ ucfirst($enrollment->status) }}</p>
                                        @if ($enrollment->program->mentor)
                                            <p class="mt-1 text-xs text-brand-deeper">Mentor: {{ $enrollment->program->mentor->name }}</p>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <p class="font-display text-2xl font-bold text-brand-deeper">{{ $enrollment->progress }}%</p>
                                        <p class="text-xs text-ink-soft">progress</p>
                                    </div>
                                </div>
                                <div class="progress-bar mt-5">
                                    <span style="width: {{ $enrollment->progress }}%"></span>
                                </div>
                                <div class="mt-5 flex flex-wrap gap-2">
                                    <a href="{{ route('learn.show', $enrollment->program) }}" class="btn-primary">Masuk kelas</a>
                                    @if ($enrollment->certificate)
                                        <a href="{{ route('learn.certificate', $enrollment->program) }}" target="_blank" class="btn-secondary">Cetak sertifikat</a>
                                    @endif
                                    @if ($enrollment->program->type === 'internship' && $enrollment->hasGrade())
                                        <a href="{{ route('internships.grade', $enrollment->program) }}" target="_blank" class="btn-secondary">Lihat & cetak nilai</a>
                                    @endif
                                    @if ($enrollment->isCompleted() && ! $enrollment->student_feedback_at)
                                        <a href="{{ route('learn.show', $enrollment->program) }}#rating" class="btn-secondary">⭐ Rate mentor</a>
                                    @endif
                                    @if ($enrollment->canWriteTestimonial())
                                        <a href="{{ route('testimonials.create', $enrollment) }}" class="btn-secondary">Tulis testimoni {{ strtolower($enrollment->program->typeLabel()) }}</a>
                                    @endif
                                    @if ($enrollment->testimonial)
                                        <span class="inline-flex items-center rounded-xl border border-brand/20 bg-brand-mist px-3 py-2 text-xs font-semibold text-ink">
                                            ✓ Testimoni terkirim
                                        </span>
                                    @endif
                                    @if ($enrollment->mentor_rating)
                                        <span class="inline-flex items-center gap-1 rounded-xl border border-ink/10 bg-surface px-3 py-2 text-xs font-semibold text-ink">
                                            Dari mentor: <x-star-display :value="$enrollment->mentor_rating" size="sm" :show-number="false" />
                                        </span>
                                    @endif
                                    @if ($enrollment->hasGrade())
                                        <span class="inline-flex items-center rounded-xl border border-brand/20 bg-brand-mist px-3 py-2 text-xs font-semibold text-ink">
                                            Nilai: {{ $enrollment->final_score }} · {{ $enrollment->grade_predicate }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <aside class="space-y-6">
            <div class="card-soft p-5">
                <h2 class="font-display text-lg font-semibold">Jadwal mendatang</h2>
                @forelse ($schedules as $schedule)
                    <div class="mt-4 border-t border-brand/10 pt-4 first:mt-3 first:border-0 first:pt-0">
                        <p class="font-medium text-ink">{{ $schedule->title }}</p>
                        <p class="text-xs text-ink-soft">{{ $schedule->program->title }}</p>
                        <p class="mt-1 text-sm text-brand-deeper">{{ $schedule->starts_at->translatedFormat('d M Y, H:i') }}</p>
                        <div class="mt-2 flex flex-wrap gap-3">
                            @if ($schedule->meeting_url)
                                <a href="{{ $schedule->meeting_url }}" target="_blank" class="text-xs font-semibold text-brand-deeper hover:underline">Join Meet →</a>
                            @endif
                            @if ($schedule->materials_url)
                                <a href="{{ $schedule->materials_url }}" target="_blank" class="text-xs font-semibold text-brand-mid hover:underline">Materi →</a>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="mt-3 text-sm text-ink-soft">Tidak ada jadwal mendatang.</p>
                @endforelse
                <a href="{{ route('schedules.index') }}" class="btn-ghost mt-4 w-full text-sm">Lihat semua jadwal</a>
            </div>

            <div class="card-soft p-5">
                <h2 class="font-display text-lg font-semibold">Notifikasi terbaru</h2>
                @forelse ($notifications as $notification)
                    <div class="mt-4 border-t border-brand/10 pt-4 first:mt-3 first:border-0 first:pt-0">
                        <p class="text-sm font-medium text-ink">{{ $notification->title }}</p>
                        <p class="mt-0.5 text-xs text-ink-soft">{{ $notification->body }}</p>
                        <p class="mt-1 text-[11px] text-ink-soft">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                @empty
                    <p class="mt-3 text-sm text-ink-soft">Belum ada notifikasi.</p>
                @endforelse
                <a href="{{ route('notifications.index') }}" class="btn-ghost mt-4 w-full text-sm">Lihat semua</a>
            </div>
        </aside>
    </div>
</section>
@endsection
