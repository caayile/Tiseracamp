@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('heading', 'Dashboard')

@section('content')
@php
    $cards = [
        'users' => ['label' => 'Siswa', 'icon' => 'M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8z', 'color' => 'bg-brand/15 text-brand-dark', 'href' => route('admin.users.index', ['role' => 'student'])],
        'mentors' => ['label' => 'Mentor', 'icon' => 'M12 14l9-5-9-5-9 5 9 5zM12 14l6.16-3.422A12.083 12.083 0 0112 21.5 12.083 12.083 0 015.84 10.578L12 14z', 'color' => 'bg-amber-100 text-amber-700', 'href' => route('admin.users.index', ['role' => 'mentor'])],
        'active_interns' => ['label' => 'Peserta magang aktif', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'color' => 'bg-rose-100 text-rose-700', 'href' => route('admin.grades.index')],
        'programs' => ['label' => 'Program', 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10', 'color' => 'bg-emerald-100 text-emerald-700', 'href' => route('admin.programs.index', ['type' => 'bootcamp'])],
        'active_enrollments' => ['label' => 'Enrollment aktif', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'color' => 'bg-sky-100 text-sky-700', 'href' => route('admin.grades.index')],
        'revenue' => ['label' => 'Revenue (Rp)', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'bg-brand/15 text-brand-dark', 'href' => route('admin.payments.index')],
        'completion_rate' => ['label' => 'Completion rate', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'bg-emerald-100 text-emerald-700', 'href' => route('admin.grades.index')],
        'tsu' => ['label' => 'Mahasiswa TSU', 'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z', 'color' => 'bg-indigo-100 text-indigo-700', 'href' => route('admin.users.index', ['tsu' => 'tsu'])],
        'non_tsu' => ['label' => 'Pengguna umum', 'icon' => 'M3 21v-2a4 4 0 014-4h4a4 4 0 014 4v2m-6-10a4 4 0 100-8 4 4 0 000 8zm12 2v2m0-6v2', 'color' => 'bg-slate-100 text-slate-700', 'href' => route('admin.users.index', ['tsu' => 'non_tsu'])],
    ];
    $stats['tsu'] = $tsuStats['tsu'];
    $stats['non_tsu'] = $tsuStats['non_tsu'];
@endphp

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach ($cards as $key => $card)
        @php $value = $stats[$key] ?? 0; @endphp
        <a href="{{ $card['href'] }}" class="card-soft group p-5 transition hover:-translate-y-0.5 hover:shadow-lg">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-soft">{{ $card['label'] }}</p>
                    <p class="mt-2 font-display text-3xl font-bold text-ink">
                        @if ($key === 'revenue')
                            {{ number_format($value, 0, ',', '.') }}
                        @elseif ($key === 'completion_rate')
                            {{ $value }}%
                        @else
                            {{ $value }}
                        @endif
                    </p>
                </div>
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $card['color'] }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}" />
                    </svg>
                </span>
            </div>
        </a>
    @endforeach
</div>

@php
    $chartMax = max(1, (int) $divisionStats->max('total'));
    $yMax = max(5, (int) ceil($chartMax / 5) * 5);
    $yStep = max(1, (int) ($yMax / 5));
    $yTicks = range($yMax, 0, -$yStep);
    $barMinWidth = $divisionStats->count() > 8 ? 2.75 : 3.5;
@endphp

<div class="card-soft mt-8 overflow-hidden p-5 sm:p-6">
    <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
        <h2 class="font-display text-lg font-semibold text-[#7A1F2B] dark:text-rose-300">Statistik Pendaftar Setiap Divisi</h2>
        <a href="{{ route('admin.applications.pendaftar') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-ink-soft transition hover:text-ink">
            Data Terkini
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M7 7h10v10"/>
            </svg>
        </a>
    </div>

    @if ($divisionStats->isEmpty())
        <p class="py-12 text-center text-sm text-ink-soft">Belum ada divisi lowongan magang.</p>
    @else
        <div class="flex gap-3">
            <div class="flex h-64 shrink-0 flex-col justify-between py-1 pr-1 text-right text-[11px] font-medium text-ink-soft">
                @foreach ($yTicks as $tick)
                    <span>{{ $tick }}</span>
                @endforeach
            </div>
            <div class="min-w-0 flex-1 overflow-x-auto">
                <div class="relative h-64" style="min-width: {{ max(28, $divisionStats->count() * $barMinWidth) }}rem">
                    <div class="absolute inset-0 flex flex-col justify-between py-1">
                        @foreach ($yTicks as $tick)
                            <div class="border-t border-ink/10"></div>
                        @endforeach
                    </div>
                    <div class="absolute inset-0 flex items-end gap-2 px-1 pb-px">
                        @foreach ($divisionStats as $row)
                            @php
                                $height = $yMax > 0 ? max($row['total'] > 0 ? 6 : 0, round(($row['total'] / $yMax) * 100, 2)) : 0;
                                $href = $row['division']
                                    ? route('admin.applications.pendaftar', ['division' => $row['division']])
                                    : route('admin.applications.pendaftar', ['q' => $row['label']]);
                            @endphp
                            <a href="{{ $href }}"
                               class="group relative flex h-full min-w-0 flex-1 flex-col justify-end"
                               title="{{ $row['label'] }}: {{ $row['total'] }} pendaftar">
                                <span class="mx-auto block w-3 rounded-t-sm bg-[#A8B89A] transition group-hover:bg-[#8FA37A] sm:w-3.5"
                                      style="height: {{ $height }}%"></span>
                            </a>
                        @endforeach
                    </div>
                </div>
                <div class="flex gap-2 px-1 pt-2" style="min-width: {{ max(28, $divisionStats->count() * $barMinWidth) }}rem; height: 6.5rem;">
                    @foreach ($divisionStats as $row)
                        <div class="relative min-w-0 flex-1">
                            <span class="absolute left-1/2 top-1 origin-top-left -rotate-45 whitespace-nowrap text-[10px] leading-tight text-ink-soft">{{ $row['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>

<div class="mt-8 grid gap-6 lg:grid-cols-2">
    <div class="card-soft overflow-hidden">
        <div class="border-b border-brand/10 px-5 py-4">
            <h2 class="font-display text-lg font-semibold">Enrollment terbaru</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-brand-mist/60 text-ink-soft">
                    <tr>
                        <th class="px-5 py-3 font-medium">Siswa</th>
                        <th class="px-5 py-3 font-medium">Program</th>
                        <th class="px-5 py-3 font-medium">Progress</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentEnrollments as $enrollment)
                        <tr class="border-t border-brand/10">
                            <td class="px-5 py-3">
                                <p class="font-medium text-ink">{{ $enrollment->user->name }}</p>
                                <p class="text-xs text-ink-soft">{{ $enrollment->user->email }}</p>
                            </td>
                            <td class="px-5 py-3">{{ $enrollment->program->title }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="h-1.5 w-16 overflow-hidden rounded-full bg-brand/10">
                                        <div class="h-full rounded-full bg-brand-mid" style="width: {{ min($enrollment->progress, 100) }}%"></div>
                                    </div>
                                    <span class="text-xs text-ink-soft">{{ $enrollment->progress }}%</span>
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                @php
                                    $badge = [
                                        'active' => ['bg-emerald-100 text-emerald-700', 'Aktif'],
                                        'completed' => ['bg-sky-100 text-sky-700', 'Selesai'],
                                        'dropped' => ['bg-rose-100 text-rose-700', 'Berhenti'],
                                    ][$enrollment->status] ?? ['bg-slate-100 text-slate-700', ucfirst($enrollment->status)];
                                @endphp
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badge[0] }}">{{ $badge[1] }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-ink-soft">Belum ada enrollment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-soft overflow-hidden">
        <div class="border-b border-brand/10 px-5 py-4">
            <h2 class="font-display text-lg font-semibold">Pembayaran pending</h2>
        </div>
        <div class="divide-y divide-brand/10">
            @forelse ($pendingPayments as $payment)
                <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4">
                    <div class="min-w-0">
                        <p class="font-medium text-ink">{{ $payment->invoice_code }}</p>
                        <p class="text-xs text-ink-soft">{{ $payment->user->name }} · {{ $payment->program->title }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-semibold text-ink">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                        <a href="{{ route('admin.payments.index') }}" class="btn-ghost text-xs">Verifikasi →</a>
                    </div>
                </div>
            @empty
                <p class="px-5 py-8 text-center text-sm text-ink-soft">Tidak ada pembayaran pending.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="card-soft mt-8 overflow-hidden">
    <div class="border-b border-brand/10 px-5 py-4">
        <h2 class="font-display text-lg font-semibold">Activity log</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-brand-mist/60 text-ink-soft">
                <tr>
                    <th class="px-5 py-3 font-medium">User</th>
                    <th class="px-5 py-3 font-medium">Aksi</th>
                    <th class="px-5 py-3 font-medium">Detail</th>
                    <th class="px-5 py-3 font-medium">Waktu</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr class="border-t border-brand/10">
                        <td class="px-5 py-3">{{ $log->user?->name ?? '—' }}</td>
                        <td class="px-5 py-3">{{ $log->action }}</td>
                        <td class="px-5 py-3 text-ink-soft">{{ $log->meta ?? ($log->subject_type ? class_basename($log->subject_type).' #'.$log->subject_id : '—') }}</td>
                        <td class="px-5 py-3 text-ink-soft">{{ $log->created_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-8 text-center text-ink-soft">Belum ada log.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
