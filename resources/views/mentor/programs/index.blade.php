@extends('layouts.mentor')

@section('title', 'Program Saya')
@section('heading', 'Bootcamp Saya')

@section('content')
<div class="mb-8 flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-brand-mid">Katalog mentor</p>
        <p class="mt-1 max-w-xl text-sm text-ink-soft">Tampilan katalog modern — foto mentor, highlight skill, harga, dan status program.</p>
    </div>
    <a href="{{ route('mentor.programs.create') }}" class="btn-navy">
        <span class="text-brand">+</span> Tambah Program
    </a>
</div>

@if ($programs->isEmpty())
    <div class="overflow-hidden rounded-2xl border border-dashed border-brand/40 bg-white p-12 text-center">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-ink font-display text-2xl text-brand">+</div>
        <p class="font-display text-xl font-semibold text-ink">Belum ada program</p>
        <p class="mt-2 text-sm text-ink-soft">Buat bootcamp pertama kamu sekarang.</p>
        <a href="{{ route('mentor.programs.create') }}" class="btn-primary mt-6">Tambah Program</a>
    </div>
@else
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
        @foreach ($programs as $program)
            @php
                $statusCta = match ($program->approval_status) {
                    'approved' => 'Program aktif · kelola sekarang',
                    'pending' => 'Menunggu approval admin',
                    'rejected' => 'Ditolak admin · edit & ajukan ulang',
                    default => 'Status: '.$program->approval_status,
                };
            @endphp
            <x-program-card
                :program="$program"
                :cta="$statusCta"
                :href="$program->approval_status === 'rejected' ? route('mentor.programs.edit', $program) : route('mentor.programs.curriculum', $program)"
                :actions="true"
            />
        @endforeach

        <a href="{{ route('mentor.programs.create') }}"
           class="flex min-h-[320px] flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed border-brand/35 bg-brand-mist/60 p-6 text-center transition hover:border-brand hover:bg-brand/10">
            <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-ink text-3xl font-light text-brand">+</span>
            <p class="font-display text-base font-semibold text-ink">Tambah Program</p>
            <p class="text-xs text-ink-soft">Foto mentor + highlight benefit</p>
        </a>
    </div>
@endif
@endsection
