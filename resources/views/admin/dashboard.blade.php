@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('heading', 'Dashboard')

@section('content')
@php
    $labels = [
        'users' => 'Siswa',
        'mentors' => 'Mentor',
        'programs' => 'Program',
        'active_enrollments' => 'Enrollment aktif',
        'revenue' => 'Revenue (Rp)',
        'completion_rate' => 'Completion rate (%)',
    ];
@endphp

<div class="mb-6 flex flex-wrap gap-2">
    <a href="{{ route('admin.programs.index', ['type' => 'internship']) }}" class="btn-secondary text-sm">Lowongan Magang</a>
    <a href="{{ route('admin.programs.create', ['type' => 'internship']) }}" class="btn-primary text-sm">Tambah Lowongan Magang</a>
    <a href="{{ route('admin.programs.create', ['type' => 'job']) }}" class="btn-secondary text-sm">Tambah Lowongan Kerja</a>
    <a href="{{ route('admin.programs.index', ['type' => 'bootcamp']) }}" class="btn-secondary text-sm">Bootcamp</a>
    <a href="{{ route('admin.applications.index') }}" class="btn-secondary text-sm">Seleksi Magang</a>
    <a href="{{ route('admin.grades.index') }}" class="btn-secondary text-sm">Nilai Magang</a>
    <a href="{{ route('admin.schedules.index') }}" class="btn-secondary text-sm">Sesi Magang</a>
    <a href="{{ route('admin.chat.index') }}" class="btn-secondary text-sm">Chat Magang</a>
    <a href="{{ route('admin.content.index') }}" class="btn-secondary text-sm">Berita</a>
    <a href="{{ route('admin.payments.index') }}" class="btn-primary text-sm">Verifikasi Bayar</a>
</div>

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    @foreach ($stats as $key => $value)
        <div class="card-soft p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-ink-soft">{{ $labels[$key] ?? $key }}</p>
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
    @endforeach
</div>

<div class="mt-6 grid gap-4 sm:grid-cols-2">
    <div class="card-soft p-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-ink-soft">Mahasiswa TSU</p>
        <p class="mt-2 font-display text-3xl font-bold text-ink">{{ $tsuStats['tsu'] }}</p>
        <a href="{{ route('admin.users.index', ['tsu' => 'tsu']) }}" class="mt-2 inline-block text-xs font-semibold text-brand-mid hover:underline">Lihat daftar →</a>
    </div>
    <div class="card-soft p-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-ink-soft">Pengguna umum</p>
        <p class="mt-2 font-display text-3xl font-bold text-ink">{{ $tsuStats['non_tsu'] }}</p>
        <a href="{{ route('admin.users.index', ['tsu' => 'non_tsu']) }}" class="mt-2 inline-block text-xs font-semibold text-brand-mid hover:underline">Lihat daftar →</a>
    </div>
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
                            <td class="px-5 py-3">{{ $enrollment->user->name }}</td>
                            <td class="px-5 py-3">{{ $enrollment->program->title }}</td>
                            <td class="px-5 py-3">{{ $enrollment->progress }}%</td>
                            <td class="px-5 py-3">{{ $enrollment->status }}</td>
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
                    <div>
                        <p class="font-medium text-ink">{{ $payment->invoice_code }}</p>
                        <p class="text-xs text-ink-soft">{{ $payment->user->name }} · {{ $payment->program->title }}</p>
                    </div>
                    <a href="{{ route('admin.payments.index') }}" class="btn-ghost text-xs">Verifikasi →</a>
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
