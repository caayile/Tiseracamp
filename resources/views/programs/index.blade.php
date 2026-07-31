@extends('layouts.app')

@section('title', 'Katalog Program')

@section('content')
<section class="mesh-bg border-b border-brand/10">
    <div class="mx-auto max-w-6xl px-4 py-12">
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-brand-mid">Katalog</p>
        <h1 class="section-title mt-2">
            {{ request('type') === 'internship' ? 'Lowongan Magang' : 'Bootcamp & Program' }}
        </h1>
        <p class="mt-3 max-w-2xl text-ink-soft">
            {{ request('type') === 'internship'
                ? 'Jelajahi program magang online bersama partner industri.'
                : 'Pilih jalur belajar yang sesuai — dari skill intensive hingga magang online bersama partner.' }}
        </p>

        <form method="GET" class="mt-8 grid gap-3 rounded-2xl border border-brand/15 bg-white/80 p-4 shadow-sm md:grid-cols-[1fr_auto_auto_auto]">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari program..." class="input-field">
            <select name="type" class="input-field">
                <option value="">Semua tipe</option>
                <option value="bootcamp" @selected(request('type') === 'bootcamp')>Bootcamp</option>
                <option value="internship" @selected(request('type') === 'internship')>Magang</option>
            </select>
            <select name="level" class="input-field">
                <option value="">Semua level</option>
                @foreach (['Beginner', 'Intermediate', 'Advanced'] as $level)
                    <option value="{{ $level }}" @selected(request('level') === $level)>{{ $level }}</option>
                @endforeach
            </select>
            <button class="btn-primary" type="submit">Filter</button>
        </form>
    </div>
</section>

<section class="mx-auto max-w-6xl px-4 py-12">
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @forelse ($programs as $program)
            <div class="reveal">
                <x-program-card :program="$program" />
            </div>
        @empty
            <div class="card-soft col-span-full p-10 text-center text-ink-soft">
                Belum ada program yang cocok dengan filter ini.
            </div>
        @endforelse
    </div>

    <div class="mt-10">
        {{ $programs->links() }}
    </div>
</section>
@endsection
