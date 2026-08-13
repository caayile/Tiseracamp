@extends('layouts.mentor')

@section('title', 'Pendaftar Magang')
@section('heading', 'Pendaftar Magang')

@section('content')
<p class="mb-4 text-sm text-ink-soft">Seleksi akhir dilakukan admin. Di sini kamu bisa pantau pendaftar program magang yang kamu dampingi.</p>
<div class="space-y-3">
    @forelse ($applications as $application)
        <div class="card-soft flex flex-wrap items-start justify-between gap-3 p-5">
            <div>
                <p class="font-semibold text-ink">{{ $application->user?->name }}</p>
                <p class="text-sm text-ink-soft">{{ $application->program?->title }}</p>
                <p class="mt-1 text-xs text-ink-soft">{{ $application->university }} · {{ $application->major }}</p>
            </div>
            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $application->statusColor() }}">{{ $application->statusLabel() }}</span>
        </div>
    @empty
        <div class="card-soft p-10 text-center">
            <p class="font-display text-lg font-semibold">Belum ada pendaftar</p>
        </div>
    @endforelse
</div>
<div class="mt-4">{{ $applications->links() }}</div>
@endsection
