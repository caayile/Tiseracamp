@extends('layouts.app')

@section('title', 'Lowongan Kerja')

@section('content')
<section class="hero-gradient border-b border-brand/10">
    <div class="mx-auto max-w-6xl px-4 py-14">
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-brand-dark">Karier</p>
        <h1 class="section-title mt-2">Lowongan Kerja</h1>
        <p class="mt-2 max-w-2xl text-ink-soft">Temukan lowongan kerja yang sesuai skill kamu.</p>
        <p class="mt-3 max-w-2xl text-sm text-ink-soft/80">Lowongan diinput admin lewat panel <strong class="text-ink">Lowongan Kerja</strong>. Untuk magang, lihat menu Magang.</p>
    </div>
</section>

<section class="mx-auto max-w-6xl px-4 py-10">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-brand-mid">Daftar pekerjaan</p>
            <h2 class="section-title mt-2 text-2xl">Temukan posisi terbaik</h2>
        </div>

        <form method="GET" class="w-full sm:w-auto">
            <div class="relative">
                <input type="search" name="q" value="{{ $search ?? '' }}" placeholder="Cari posisi, divisi, atau lokasi"
                       class="input-field w-full pr-12 pl-4 py-3 text-sm" />
                <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 rounded-full bg-brand px-3 py-2 text-sm font-semibold text-ink transition hover:bg-brand-light">
                    Cari
                </button>
            </div>
        </form>
    </div>

    <div class="mt-10 grid gap-5 lg:grid-cols-2">
        @forelse ($programs as $program)
            <div class="reveal">
                <x-program-card :program="$program" />
            </div>
        @empty
            <div class="card-soft col-span-full p-10 text-center text-ink-soft">
                Tidak ada lowongan kerja yang cocok dengan pencarian ini.
            </div>
        @endforelse
    </div>

    <div class="mt-10 flex justify-center">
        {{ $programs->links() }}
    </div>
</section>
@endsection
