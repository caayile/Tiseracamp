@extends('layouts.app')

@section('title', $catalogType === 'internship' ? 'Lowongan Magang' : 'Bootcamp')

@section('content')
<section class="mesh-bg border-b border-brand/10">
    <div class="mx-auto max-w-6xl px-4 py-12">
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-brand-mid">Katalog</p>
        <h1 class="section-title mt-2">
            {{ $catalogType === 'internship' ? 'Lowongan Magang' : 'Bootcamp' }}
        </h1>
        <p class="mt-3 max-w-2xl text-ink-soft">
            {{ $catalogType === 'internship'
                ? 'Jelajahi lowongan magang bersama partner industri.'
                : 'Pilih jalur belajar yang sesuai — skill intensive bersama mentor industri.' }}
        </p>

        @if ($catalogType === 'internship')
            <div class="mt-10 max-w-5xl mx-auto">
                <!-- Judul Utama -->
                <h2 class="text-2xl font-bold text-ink text-center mb-10">1 Bulan Durasi Program</h2>

                <!-- Timeline Wrapper -->
                <div class="relative">
                    
                    <!-- Garis Timeline Background -->
                    <div class="absolute top-[2.25rem] left-[12.5%] right-[12.5%] h-1.5 bg-brand/15 -translate-y-1/2 z-0 rounded-full" aria-hidden="true"></div>
                    
                    <!-- Garis Aktif (Teal) - untuk step 1-2 (50%) -->
                    <div class="absolute top-[2.25rem] left-[12.5%] w-[50%] h-1.5 bg-teal-500 -translate-y-1/2 z-10"></div>
                    
                    <!-- Garis Aktif (Amber dengan glow) - untuk step 3 (25%) -->
                    <div class="absolute top-[2.25rem] left-[62.5%] w-[25%] h-1.5 bg-amber-400 -translate-y-1/2 z-10 shadow-[0_0_12px_rgba(251,191,36,0.6)]"></div>

                    <!-- Grid Langkah / Steps - HORIZONTAL (4 kolom), scroll horizontal di mobile -->
                    <div class="grid grid-cols-4 gap-4 relative z-20 overflow-x-auto pb-4 scrollbar-hide">
                        @php
                            $steps = [
                                [
                                    'week' => 1,
                                    'duration' => 'Est. 1 Minggu',
                                    'title' => 'Onboarding & Learning Path',
                                    'desc' => 'Pengenalan program, mentor, dan lingkungan kerja. Mulai mempelajari learning path sesuai divisi masing-masing.',
                                    'borderColor' => 'border-teal-500',
                                    'textColor' => 'text-teal-600',
                                ],
                                [
                                    'week' => 2,
                                    'duration' => 'Est. 1 Minggu',
                                    'title' => 'Learning & Project Development',
                                    'desc' => 'Melanjutkan learning path dan mulai mengerjakan project dengan bimbingan mentor.',
                                    'borderColor' => 'border-teal-500',
                                    'textColor' => 'text-teal-600',
                                ],
                                [
                                    'week' => 3,
                                    'duration' => 'Est. 1 Minggu',
                                    'title' => 'Project Development & Review',
                                    'desc' => 'Melanjutkan pengerjaan project dan melakukan review bersama mentor untuk mendapatkan feedback dan arahan.',
                                    'borderColor' => 'border-amber-400',
                                    'textColor' => 'text-amber-600',
                                    'glow' => true,
                                ],
                                [
                                    'week' => 4,
                                    'duration' => 'Est. 1 Minggu',
                                    'title' => 'Final Project & Presentation',
                                    'desc' => 'Menyelesaikan project, melakukan presentasi, dan mendapatkan sertifikat setelah menyelesaikan program.',
                                    'borderColor' => 'border-brand/30',
                                    'textColor' => 'text-ink',
                                ],
                            ];
                        @endphp
                        @foreach ($steps as $step)
                            <div class="flex flex-col items-center text-center min-w-0">
                                <!-- Step number on timeline line -->
                                <div class="relative mb-4 flex justify-center">
                                    <div class="relative flex h-8 w-8 items-center justify-center rounded-full bg-panel border-2 {{ $step['borderColor'] }} {{ $step['textColor'] }} font-semibold z-10 shadow-sm{{ ($step['glow'] ?? false) ? ' shadow-[0_0_8px_rgba(251,191,36,0.5)]' : '' }}">
                                        <span class="text-base">{{ $step['week'] }}</span>
                                    </div>
                                </div>
                                
                                <!-- Duration label -->
                                <p class="mb-1 text-[11px] font-medium text-ink-soft">{{ $step['duration'] }}</p>
                                
                                <!-- Phase title -->
                                <h3 class="mb-1.5 font-semibold text-sm text-ink">{{ $step['title'] }}</h3>
                                
                                <!-- Description -->
                                <p class="text-[11px] text-ink-soft leading-relaxed">{{ $step['desc'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <form method="GET" class="mt-10">
            @if ($catalogType === 'internship')
                <input type="hidden" name="type" value="internship">
            @endif
            @if (! empty($activeCategory))
                <input type="hidden" name="category" value="{{ $activeCategory }}">
            @endif

            <div class="flex items-center gap-2 rounded-full border border-ink/10 bg-panel p-1.5 pl-4 shadow-[0_18px_40px_-24px_rgba(11,31,42,0.4)] sm:gap-3 sm:p-2 sm:pl-6">
                <input type="search" name="q" value="{{ request('q') }}"
                       placeholder="{{ $catalogType === 'internship' ? 'Role, kata kunci, divisi...' : 'Judul, kata kunci, skill...' }}"
                       class="min-w-0 flex-1 bg-transparent py-2.5 text-sm font-medium text-ink outline-none placeholder:text-ink-soft/55 sm:text-[15px]">

                <button type="submit"
                        class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-brand text-ink transition hover:bg-brand-light sm:h-12 sm:w-12"
                        aria-label="Cari">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"/><path d="M20 20l-3-3" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</section>

<section class="mx-auto max-w-6xl px-4 py-12">
    @if (($categories ?? collect())->isNotEmpty() && $catalogType === 'bootcamp')
        <div class="mb-8 flex flex-wrap gap-2">
            <a href="{{ route('programs.index') }}"
               class="rounded-full px-4 py-2 text-sm font-semibold transition {{ blank($activeCategory ?? null) ? 'bg-brand text-ink' : 'border border-brand/25 text-ink-soft hover:border-brand/60 hover:text-ink' }}">
                Semua
            </a>
            @foreach ($categories as $category)
                <a href="{{ route('programs.index', ['category' => $category->slug]) }}"
                   class="rounded-full px-4 py-2 text-sm font-semibold transition {{ ($activeCategory ?? null) === $category->slug ? 'bg-brand text-ink' : 'border border-brand/25 text-ink-soft hover:border-brand/60 hover:text-ink' }}">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>
    @endif

    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @forelse ($programs as $program)
            <div class="reveal">
                <x-program-card :program="$program" />
            </div>
        @empty
            <div class="card-soft col-span-full p-10 text-center text-ink-soft">
                Belum ada {{ $catalogType === 'internship' ? 'lowongan magang' : 'bootcamp' }} yang cocok dengan pencarian ini.
            </div>
        @endforelse
    </div>

    <div class="mt-10">
        {{ $programs->links() }}
    </div>
</section>
@endsection
