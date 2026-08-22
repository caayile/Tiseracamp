@extends('layouts.mentor')

@section('title', 'Magang Saya')
@section('heading', 'Magang Saya')

@section('content')
<div class="mb-8 flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-brand-mid">Penugasan admin</p>
        <p class="mt-1 max-w-xl text-sm text-ink-soft">Program magang yang ditugaskan admin untuk kamu kelola materinya.</p>
    </div>
</div>

@if ($programs->isEmpty())
    <div class="overflow-hidden rounded-2xl border border-dashed border-brand/40 bg-white p-12 text-center">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-ink font-display text-2xl text-brand">M</div>
        <p class="font-display text-xl font-semibold text-ink">Belum ada penugasan magang</p>
        <p class="mt-2 text-sm text-ink-soft">Admin akan menugaskan kamu sebagai mentor saat membuka lowongan magang.</p>
    </div>
@else
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
        @foreach ($programs as $program)
            @php
                $statusCta = $program->division
                    ? 'Divisi '.$program->division.' · kelola materi'
                    : 'Kelola materi magang';
            @endphp
            <x-program-card
                :program="$program"
                :cta="$statusCta"
                :href="route('mentor.internships.curriculum', $program)"
                :actions="true"
                variant="catalog"
            />
        @endforeach
    </div>
@endif
@endsection
