@extends('layouts.mentor')

@section('title', 'Magang Saya')
@section('heading', 'Magang Saya')

@section('content')
<div class="mb-8 flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-brand-mid">Panel magang</p>
        <p class="mt-1 max-w-xl text-sm text-ink-soft">Buat magang sendiri atau ambil yang belum ada mentor — langsung isi materi Minggu 1–4, tanpa menunggu admin.</p>
    </div>
    <a href="{{ route('mentor.internships.create') }}" class="btn-primary">+ Tambah magang</a>
</div>

@if ($programs->isEmpty())
    <div class="mb-8 overflow-hidden rounded-2xl border border-dashed border-brand/40 bg-white p-10 text-center">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-ink font-display text-2xl text-brand">M</div>
        <p class="font-display text-xl font-semibold text-ink">Belum ada magang di panelmu</p>
        <p class="mt-2 text-sm text-ink-soft">Buat lowongan baru, atau ambil magang yang masih kosong di bawah.</p>
        <a href="{{ route('mentor.internships.create') }}" class="btn-primary mt-6 inline-flex">+ Tambah magang</a>
    </div>
@else
    <div class="mb-10 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($programs as $program)
            @php
                $quota = $program->internshipQuota();
                $filled = $program->acceptedInternCount();
                $remaining = $program->remainingInternshipSeats();
                $percent = $quota ? min(100, (int) round(($filled / $quota) * 100)) : 0;
            @endphp
            <article class="card-soft flex flex-col p-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-display text-lg font-semibold text-ink">{{ $program->title }}</p>
                        <p class="mt-1 text-xs text-ink-soft">
                            {{ $program->division ?: 'Tanpa divisi' }}
                            · {{ $program->internshipStatusLabel() }}
                        </p>
                    </div>
                    <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide {{ $program->isInternshipOpen() ? 'bg-emerald-100 text-emerald-800' : 'bg-ink/10 text-ink-soft' }}">
                        {{ $program->internshipStatusLabel() }}
                    </span>
                </div>

                <div class="mt-4 rounded-xl border border-brand/15 bg-brand-mist/40 p-3">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-semibold text-ink">Kuota peserta</span>
                        <span class="text-ink-soft">{{ $program->internshipQuotaLabel() }}</span>
                    </div>
                    @if ($quota)
                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-white">
                            <div class="h-full rounded-full {{ $remaining === 0 ? 'bg-red-400' : 'bg-brand-mid' }}" style="width: {{ $percent }}%"></div>
                        </div>
                        <p class="mt-1.5 text-[11px] text-ink-soft">
                            @if ($remaining === 0)
                                Kuota penuh
                            @else
                                Sisa {{ $remaining }} kursi
                            @endif
                        </p>
                    @else
                        <p class="mt-1.5 text-[11px] text-ink-soft">Atur kuota lewat Edit magang.</p>
                    @endif
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ route('mentor.internships.curriculum', $program) }}" class="btn-primary text-xs">Isi materi</a>
                    <a href="{{ route('mentor.internships.edit', $program) }}" class="btn-secondary text-xs">Edit &amp; kuota</a>
                    <a href="{{ route('programs.show', $program->slug) }}" class="btn-ghost text-xs" target="_blank">Lihat</a>
                </div>
            </article>
        @endforeach
    </div>
@endif

@if ($available->isNotEmpty())
    <div class="mb-4">
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-brand-mid">Belum ada mentor</p>
        <p class="mt-1 text-sm text-ink-soft">Ambil magang ini, lalu langsung isi Minggu 1–4. Tidak perlu menunggu admin.</p>
    </div>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($available as $program)
            <article class="card-soft flex flex-col p-5">
                <p class="font-display text-lg font-semibold text-ink">{{ $program->title }}</p>
                <p class="mt-1 text-xs text-ink-soft">
                    {{ $program->division ?: 'Tanpa divisi' }}
                    · {{ $program->internshipQuotaLabel() }}
                </p>
                <form method="POST" action="{{ route('mentor.internships.claim', $program) }}" class="mt-4">
                    @csrf
                    <button class="btn-primary w-full text-xs" type="submit">Ambil &amp; isi materi</button>
                </form>
            </article>
        @endforeach
    </div>
@endif
@endsection
