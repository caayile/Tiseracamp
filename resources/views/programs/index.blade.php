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

        <form method="GET" class="mt-8">
            @if ($catalogType === 'internship')
                <input type="hidden" name="type" value="internship">
                @if ($isTsuStudent)
                    <input type="hidden" name="scope" value="{{ $scope }}">
                @endif
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
    @if ($catalogType === 'internship' && auth()->user()?->isTsuPending())
        <div class="mb-8 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            Filter <strong>TS Group</strong> aktif setelah admin menyetujui KTM. Sementara ini kamu melihat lowongan umum.
        </div>
    @endif
    @if ($catalogType === 'internship' && $isTsuStudent)
        <div class="mb-8 flex flex-wrap gap-2">
            <a href="{{ route('programs.index', ['type' => 'internship', 'scope' => 'all']) }}"
               class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $scope === 'all' ? 'bg-brand text-ink' : 'border border-brand/25 text-ink-soft hover:border-brand/60 hover:text-ink' }}">
                Semua Lowongan
            </a>
            <a href="{{ route('programs.index', ['type' => 'internship', 'scope' => 'tsu']) }}"
               class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $scope === 'tsu' ? 'bg-brand text-ink' : 'border border-brand/25 text-ink-soft hover:border-brand/60 hover:text-ink' }}">
                TS Group
            </a>
        </div>
    @endif

    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @forelse ($programs as $program)
            <div class="reveal">
                <x-program-card :program="$program" :scope="$scope" />
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
