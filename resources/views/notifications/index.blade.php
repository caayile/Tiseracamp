@extends(auth()->user()->isAdmin() ? 'layouts.admin' : (auth()->user()->isMentor() ? 'layouts.mentor' : 'layouts.app'))

@section('title', 'Notifikasi')
@section('heading', 'Notifikasi')

@section('content')
@unless(auth()->user()->isAdmin() || auth()->user()->isMentor())
<section class="mesh-bg border-b border-brand/10">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <x-back-nav :fallback="route('dashboard')" force class="mb-4" />
        <h1 class="section-title">Notifikasi</h1>
        <p class="mt-2 text-ink-soft">Klik notifikasi untuk membacanya — otomatis ditandai sudah dibaca.</p>
    </div>
</section>
@endunless

<section class="{{ auth()->user()->isAdmin() || auth()->user()->isMentor() ? '' : 'mx-auto max-w-3xl px-4 py-10' }}">
    @if (! (auth()->user()->isAdmin() || auth()->user()->isMentor()))
        {{-- student layout already has padding from section --}}
    @else
        <p class="mb-4 text-sm text-ink-soft">Klik notifikasi untuk membacanya — otomatis ditandai sudah dibaca.</p>
    @endif

    @if ($notifications->isEmpty())
        <div class="card-soft p-10 text-center">
            <p class="font-display text-xl font-semibold text-ink">Tidak ada notifikasi</p>
            <p class="mt-2 text-sm text-ink-soft">Notifikasi baru akan muncul di sini.</p>
        </div>
    @else
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-ink-soft">{{ $notifications->total() }} notifikasi</p>
            @if ($notifications->contains(fn ($n) => ! $n->read_at))
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="btn-secondary text-xs">Tandai semua sudah dibaca</button>
                </form>
            @endif
        </div>

        <div class="space-y-3">
            @foreach ($notifications as $notification)
                @php
                    $isHighlight = (int) ($highlightId ?? 0) === (int) $notification->id;
                @endphp
                <a href="{{ route('notifications.open', $notification) }}"
                   id="notif-{{ $notification->id }}"
                   class="card-soft block p-4 transition hover:border-brand/30 hover:shadow-md {{ $notification->read_at ? 'opacity-80' : 'border-brand/30 bg-brand-mist/30' }} {{ $isHighlight ? 'ring-2 ring-brand/50' : '' }}">
                    <div class="flex items-start gap-3">
                        <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full {{ $notification->read_at ? 'bg-ink/20' : 'bg-brand' }}"></span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-semibold text-ink">{{ $notification->title }}</p>
                                @unless ($notification->read_at)
                                    <span class="rounded-full bg-brand/20 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-brand-deeper">Baru</span>
                                @endunless
                            </div>
                            @if (filled($notification->body))
                                <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-ink-soft">{{ $notification->body }}</p>
                            @endif
                            <p class="mt-2 text-xs text-ink-soft">
                                {{ $notification->created_at->diffForHumans() }}
                                @if ($notification->link)
                                    <span class="mx-1">·</span>
                                    <span class="font-medium text-brand-mid">Buka detail terkait →</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-6">{{ $notifications->links() }}</div>
    @endif
</section>

@if (! empty($highlightId))
<script>
    document.getElementById('notif-{{ (int) $highlightId }}')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
</script>
@endif
@endsection
