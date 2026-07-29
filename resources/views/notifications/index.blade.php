@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
<section class="mesh-bg border-b border-brand/10">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <h1 class="section-title">Notifikasi</h1>
        <p class="mt-2 text-ink-soft">Update terbaru tentang kelas, pembayaran, dan pesan.</p>
    </div>
</section>

<section class="mx-auto max-w-3xl px-4 py-10">
    @if ($notifications->isEmpty())
        <div class="card-soft p-10 text-center">
            <p class="font-display text-xl font-semibold">Tidak ada notifikasi</p>
            <p class="mt-2 text-sm text-ink-soft">Notifikasi baru akan muncul di sini.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($notifications as $notification)
                <div class="card-soft flex items-start justify-between gap-4 p-4 {{ $notification->read_at ? 'opacity-70' : 'border-brand/30' }}">
                    <div>
                        <p class="font-semibold text-ink">{{ $notification->title }}</p>
                        <p class="mt-1 text-sm text-ink-soft">{{ $notification->body }}</p>
                        <p class="mt-2 text-xs text-ink-soft">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    @if ($notification->link && ! $notification->read_at)
                        <form method="POST" action="{{ route('notifications.read', $notification) }}">
                            @csrf
                            <button class="btn-ghost text-xs shrink-0" type="submit">Baca</button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-6">{{ $notifications->links() }}</div>
    @endif
</section>
@endsection
