@extends('layouts.app')

@section('title', $program->title)

@section('content')
<section class="hero-gradient border-b border-brand/10">
    <div class="mx-auto grid max-w-6xl gap-10 px-4 py-14 md:grid-cols-[1.2fr_0.8fr] md:items-start">
        <div class="reveal">
            <x-back-nav
                :fallback="$program->type === 'internship' ? route('programs.index', ['type' => 'internship']) : route('programs.index')"
                class="mb-4"
            />
            <div class="flex flex-wrap gap-2">
                <span class="badge">{{ $program->typeLabel() }}</span>
                <span class="rounded-lg bg-white/70 px-2.5 py-1 text-xs font-medium text-ink-soft">{{ $program->level }}</span>
                <span class="rounded-lg bg-white/70 px-2.5 py-1 text-xs font-medium text-ink-soft">{{ $program->formattedDuration() }}</span>
            </div>
            <h1 class="mt-4 font-display text-3xl font-bold text-ink md:text-5xl">{{ $program->title }}</h1>
            <p class="mt-4 max-w-2xl text-base leading-relaxed text-ink-soft">{{ $program->excerpt }}</p>
            @if ($program->partner)
                <p class="mt-4 text-sm font-medium text-brand-deeper">Partner: {{ $program->partner->name }}</p>
            @endif
            @if ($program->mentor)
                <div class="mt-4 flex items-center gap-3 rounded-xl border border-brand/15 bg-white/60 p-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand/20 font-display text-sm font-bold text-brand-deeper">
                        {{ strtoupper(substr($program->mentor->name, 0, 2)) }}
                    </span>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-ink-soft">Mentor</p>
                        <p class="font-semibold text-ink">{{ $program->mentor->name }}</p>
                        @if ($program->mentor->expertise)
                            <p class="text-xs text-ink-soft">{{ collect($program->mentor->expertise)->implode(', ') }}</p>
                        @endif
                        @if ($program->mentor->rating)
                            <p class="text-xs text-brand-deeper">{{ number_format($program->mentor->rating, 1) }} ★ rating</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <div class="card-soft reveal p-6">
            <p class="text-sm text-ink-soft">Investasi program</p>
            <p class="mt-1 font-display text-3xl font-bold text-ink">{{ $program->formattedPrice() }}</p>
            <ul class="mt-5 space-y-2">
                @foreach (($program->benefits ?? []) as $benefit)
                    <li class="flex items-start gap-2 text-sm text-ink">
                        <span class="mt-1 h-2 w-2 rounded-full bg-brand"></span>
                        {{ $benefit }}
                    </li>
                @endforeach
            </ul>

            <div class="mt-6">
                @auth
                    @if ($enrolled)
                        <a href="{{ route('learn.show', $program) }}" class="btn-primary w-full">Lanjut belajar</a>
                    @elseif ($program->type === 'internship' && $application)
                        @if ($application->status === 'accepted')
                            <a href="{{ route('learn.show', $program) }}" class="btn-primary w-full">Mulai magang</a>
                        @else
                            <a href="{{ route('internships.status', $program) }}" class="btn-primary w-full">Lihat status seleksi</a>
                            <p class="mt-2 text-center text-xs text-ink-soft">{{ $application->statusLabel() }}</p>
                        @endif
                    @elseif ($program->type === 'internship')
                        <a href="{{ route('internships.apply', $program) }}" class="btn-primary w-full">Daftar magang</a>
                    @else
                        <form method="POST" action="{{ route('programs.enroll', $program) }}">
                            @csrf
                            <button class="btn-primary w-full" type="submit">Daftar program</button>
                        </form>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="btn-primary w-full">Masuk untuk daftar</a>
                @endauth
            </div>
        </div>
    </div>
</section>

<section class="mx-auto grid max-w-6xl gap-8 px-4 py-14 lg:grid-cols-[1.2fr_0.8fr]">
    <div class="reveal space-y-6">
        @if ($program->type === 'internship')
            <div class="overflow-hidden rounded-3xl bg-[#E8F2EC] p-6 sm:p-8">
                <h2 class="text-center font-display text-2xl font-bold text-[#3D4A2E]">Proses Pendaftaran Magang</h2>
                <p class="mt-2 text-center text-sm text-[#5C6B4A]">Ikuti langkah-langkah berikut untuk bergabung dalam program magang kami</p>
                @php
                    $processSteps = [
                        ['Isi Formulir', 'Lengkapi data diri'],
                        ['Unggah Dokumen', 'Upload berkas persyaratan'],
                        ['Seleksi', 'Proses peninjauan'],
                        ['Pengumuman', 'Hasil seleksi'],
                        ['Mulai Magang', 'Onboarding program'],
                    ];
                @endphp
                <div class="relative mx-auto mt-8 max-w-4xl">
                    <div class="absolute left-[10%] right-[10%] top-6 hidden h-px bg-[#8B7355]/35 md:block"></div>
                    <div class="grid grid-cols-2 gap-5 md:grid-cols-5">
                        @foreach ($processSteps as $i => $step)
                            <div class="text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full border-2 border-[#8B7355] bg-white text-sm font-bold text-[#3D4A2E]">{{ $i + 1 }}</div>
                                <p class="mt-3 text-sm font-semibold text-[#3D4A2E]">{{ $step[0] }}</p>
                                <p class="mt-1 text-xs text-[#5C6B4A]">{{ $step[1] }}</p>
                                <p class="mt-2 text-[10px] font-semibold uppercase tracking-wide text-[#8B7355]">Step {{ $i + 1 }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <div class="card-soft p-6">
            <h2 class="font-display text-xl font-semibold">Tentang program</h2>
            <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-ink-soft">{{ $program->description }}</p>
        </div>

        <div class="card-soft p-6">
            <h2 class="font-display text-xl font-semibold">Silabus</h2>
            <div class="mt-4 space-y-4">
                @foreach ($program->modules as $module)
                    <div class="rounded-xl border border-brand/15 bg-brand-mist/40 p-4">
                        <p class="font-semibold text-ink">{{ $module->sort_order }}. {{ $module->title }}</p>
                        <ul class="mt-3 space-y-2">
                            @foreach ($module->lessons as $lesson)
                                <li class="flex items-center justify-between gap-3 text-sm text-ink-soft">
                                    <span>{{ $lesson->title }}</span>
                                    <span class="text-xs uppercase tracking-wide">{{ $lesson->type }} · {{ $lesson->duration_minutes }}m</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <aside class="reveal space-y-4">
        <div class="card-soft p-6">
            <h3 class="font-display text-lg font-semibold">Yang akan kamu dapat</h3>
            <p class="mt-2 text-sm text-ink-soft">Kurikulum terstruktur, progress tracking, dan sertifikat setelah menyelesaikan seluruh materi.</p>
        </div>
    </aside>
</section>
@endsection
