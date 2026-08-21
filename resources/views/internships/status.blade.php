@extends('layouts.app')

@section('title', 'Status Pendaftaran — '.$program->title)

@section('content')
<section class="mx-auto max-w-5xl px-4 py-10">
    <div class="mb-6">
        <x-back-nav :fallback="route('programs.show', $program->slug)" />
        <h1 class="mt-2 font-display text-2xl font-semibold text-ink">Status Pendaftaran</h1>
        <p class="mt-1 text-sm text-ink-soft">{{ $program->title }}</p>
    </div>

    @php
        $statusUrl = route('internships.status', $program);
        $activeStep = (int) request('step');
        if (! in_array($activeStep, [1, 2, 3, 4], true)) {
            $activeStep = 0;
        }
        $stepUrl = fn (int $n) => $statusUrl.'?step='.$n;
    @endphp

    <div class="grid gap-6 lg:grid-cols-3 lg:items-start">
        <aside class="card-soft p-6 lg:sticky lg:top-6">
            <p class="mb-4 text-xs font-semibold uppercase tracking-wide text-ink-soft">Alur pendaftaran magang</p>
            <x-vertical-stepper
                :steps="$application->stepperSteps()"
                :step-links="[1 => $stepUrl(1), 2 => $stepUrl(2), 3 => $stepUrl(3), 4 => $stepUrl(4)]"
                :active-step="$activeStep ?: null"
            />
        </aside>

        @if ($activeStep === 1)
            {{-- Step 1: Review Mode — isi formulir (read-only) --}}
            <div class="card-soft space-y-6 p-6 lg:col-span-2">
                <div>
                    <span class="rounded-full bg-brand/15 px-3 py-1 text-xs font-bold text-brand-dark">Tahap 1 dari 4 · Review</span>
                    <h2 class="mt-3 font-display text-xl font-semibold text-ink">Isi formulir yang kamu kirim</h2>
                    <p class="mt-1 text-sm text-ink-soft">Periksa kembali data & dokumen yang sudah terkirim untuk {{ $program->title }}.</p>
                </div>

                <div class="border-t border-brand/10 pt-6">
                    @include('internships._application_summary', ['application' => $application])
                </div>

                @if ($application->submittedAtLabel())
                    <p class="text-xs text-ink-soft">Dikirim: {{ $application->submittedAtLabel() }}</p>
                @endif
            </div>
        @elseif ($activeStep === 2)
            {{-- Step 2: Seleksi Administrasi --}}
            <div class="card-soft space-y-6 p-6 lg:col-span-2">
                <div>
                    <span class="rounded-full bg-brand/15 px-3 py-1 text-xs font-bold text-brand-dark">Tahap 2 dari 4</span>
                    <h2 class="mt-3 font-display text-xl font-semibold text-ink">Seleksi Administrasi</h2>
                    <p class="mt-1 text-sm leading-relaxed text-ink-soft">{{ $application->seleksiPanelIntro() }}</p>
                </div>

                <div class="border-t border-brand/10 pt-6">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-soft">Yang kami periksa</p>
                    <ul class="mt-3 space-y-2.5">
                        @foreach ($application->seleksiChecklist() as $item)
                            <li class="flex items-center gap-3 text-sm">
                                @if ($item['state'] === 'done')
                                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700"><svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></span>
                                    <span class="text-ink">{{ $item['label'] }} <span class="text-xs text-emerald-700">· lengkap</span></span>
                                @elseif ($item['state'] === 'failed')
                                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600"><svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></span>
                                    <span class="text-ink">{{ $item['label'] }} <span class="text-xs text-red-600">· belum sesuai</span></span>
                                @elseif ($item['state'] === 'missing')
                                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700"><svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg></span>
                                    <span class="text-ink">{{ $item['label'] }} <span class="text-xs text-amber-700">· tidak ditemukan</span></span>
                                @else
                                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-ink/10 text-ink-soft"><svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7v5l3 2"/></svg></span>
                                    <span class="text-ink">{{ $item['label'] }} <span class="text-xs text-ink-soft">· sedang diperiksa</span></span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="rounded-xl bg-panel px-4 py-3 text-sm text-ink-soft">
                    {{ match ($application->status) {
                        'submitted' => 'Berkas kamu sedang antre untuk diperiksa tim admin.',
                        'under_review' => 'Tim sedang meninjau berkasmu lebih detail.',
                        'accepted' => 'Berkasmu dinyatakan lolos verifikasi'
                            .($application->reviewedAtLabel() ? ' pada '.$application->reviewedAtLabel().'.' : '.'),
                        'rejected' => 'Berkasmu belum memenuhi kualifikasi program ini.',
                        default => '',
                    } }}
                </div>
            </div>
        @elseif ($activeStep === 3)
            {{-- Step 3: Pengumuman Hasil Seleksi --}}
            <div class="card-soft space-y-6 p-6 lg:col-span-2">
                <div>
                    <span class="rounded-full bg-brand/15 px-3 py-1 text-xs font-bold text-brand-dark">Tahap 3 dari 4</span>
                    <h2 class="mt-3 font-display text-xl font-semibold text-ink">Pengumuman Hasil Seleksi</h2>
                    <p class="mt-1 text-sm leading-relaxed text-ink-soft">Hasil seleksi dikabarkan lewat notifikasi akun dan halaman ini. Pastikan notifikasi aktif supaya tidak terlewat.</p>
                </div>

                <div class="border-t border-brand/10 pt-6">
                    @if ($application->status === 'accepted')
                        <div class="rounded-xl bg-emerald-50 p-5">
                            <p class="font-display text-lg font-semibold text-emerald-800">{{ $application->statusHeadline() }}</p>
                            <p class="mt-1.5 text-sm leading-relaxed text-emerald-800/80">{{ $application->statusMessage() }}</p>
                        </div>
                    @elseif ($application->status === 'rejected')
                        <div class="rounded-xl bg-red-50 p-5">
                            <p class="font-display text-lg font-semibold text-red-700">{{ $application->statusHeadline() }}</p>
                            <p class="mt-1.5 text-sm leading-relaxed text-red-700/80">{{ $application->statusMessage() }}</p>
                        </div>
                    @else
                        <div class="rounded-xl bg-panel p-5">
                            <p class="font-display text-lg font-semibold text-ink">Hasil belum diumumkan</p>
                            <p class="mt-1.5 text-sm leading-relaxed text-ink-soft">Tenang, hasilnya pasti kami kabarin begitu proses administrasi selesai. Sementara itu, kamu bisa cek kondisi berkas di tahap seleksi.</p>
                        </div>
                    @endif
                </div>
            </div>
        @elseif ($activeStep === 4)
            {{-- Step 4: Mulai Program Magang --}}
            <div class="card-soft space-y-6 p-6 lg:col-span-2">
                <div>
                    <span class="rounded-full bg-brand/15 px-3 py-1 text-xs font-bold text-brand-dark">Tahap 4 dari 4</span>
                    <h2 class="mt-3 font-display text-xl font-semibold text-ink">Mulai Program Magang</h2>
                    <p class="mt-1 text-sm leading-relaxed text-ink-soft">Setelah dinyatakan diterima, kamu tinggal masuk ke program: onboarding, perkenalan dengan mentor pembimbing, lalu memulai aktivitas magang sesuai divisi.</p>
                </div>

                <div class="border-t border-brand/10 pt-6">
                    @if ($application->status === 'accepted')
                        <div class="rounded-xl bg-brand-mist p-5">
                            <p class="font-display text-lg font-semibold text-ink">Semua siap!</p>
                            <p class="mt-1.5 text-sm leading-relaxed text-ink-soft">Kamu sudah resmi diterima. Klik tombol di bawah untuk masuk ke {{ $program->title }} dan berkenalan dengan mentormu.</p>
                            @if ($application->internshipPeriodLabel())
                                <p class="mt-2 text-xs font-medium text-ink">Periode magang: {{ $application->internshipPeriodLabel() }}</p>
                            @endif
                            <a href="{{ route('learn.show', $program) }}" class="btn-primary mt-4">Mulai magang</a>
                        </div>
                    @else
                        <div class="rounded-xl bg-panel p-5">
                            <p class="font-display text-lg font-semibold text-ink">Terkunci sementara</p>
                            <p class="mt-1.5 text-sm leading-relaxed text-ink-soft">Tahap ini terbuka setelah kamu dinyatakan diterima lewat pengumuman hasil seleksi. Pantau terus notifikasimu ya.</p>
                        </div>
                    @endif
                </div>
            </div>
        @else
            {{-- Ringkasan status (default) --}}
            <div class="card-soft space-y-6 p-6 lg:col-span-2">
                <div>
                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $application->statusColor() }}">{{ $application->statusLabel() }}</span>
                    <h2 class="mt-3 font-display text-xl font-semibold text-ink">{{ $application->statusHeadline() }}</h2>
                    <p class="mt-2 text-sm leading-relaxed text-ink-soft">{{ $application->statusMessage() }}</p>
                </div>

                @if ($application->submitted_at || $application->reviewed_at)
                    <div class="flex flex-wrap gap-x-6 gap-y-1 rounded-xl bg-panel px-4 py-3 text-xs text-ink-soft">
                        @if ($application->submittedAtLabel())
                            <span>Dikirim: {{ $application->submittedAtLabel() }}</span>
                        @endif
                        @if ($application->reviewedAtLabel())
                            <span>Ditinjau: {{ $application->reviewedAtLabel() }}</span>
                        @endif
                    </div>
                @endif

                @if ($application->reviewer_note)
                    <div class="rounded-xl bg-brand-mist p-4 text-sm text-ink">
                        <p class="font-semibold">Catatan reviewer</p>
                        <p class="mt-1 text-ink-soft">{{ $application->reviewer_note }}</p>
                    </div>
                @endif

                <div class="flex flex-wrap gap-3 border-t border-brand/10 pt-6">
                    @if ($application->status === 'accepted')
                        <a href="{{ route('learn.show', $program) }}" class="btn-primary">Mulai magang</a>
                    @elseif ($application->status === 'rejected')
                        <a href="{{ route('programs.index', ['type' => 'internship']) }}" class="btn-secondary">Lihat magang lain</a>
                    @endif
                    <a href="{{ route('profile.applications') }}" class="btn-ghost">Riwayat pendaftaran</a>
                </div>
            </div>
        @endif
    </div>
</section>
@endsection
