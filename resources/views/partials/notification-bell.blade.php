@auth
@php
    $cacheKey = 'notif-bell-'.auth()->id();
    $bellRows = \Illuminate\Support\Facades\Cache::get($cacheKey);

    if (! is_array($bellRows)) {
        $bellRows = \App\Models\AppNotification::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->limit(5)
            ->get(['id', 'title', 'body', 'link', 'read_at', 'created_at'])
            ->map(fn ($n) => [
                'id' => $n->id,
                'title' => $n->title,
                'body' => $n->body,
                'link' => $n->link,
                'read_at' => $n->read_at?->toIso8601String(),
                'created_at' => $n->created_at?->toIso8601String(),
            ])
            ->all();

        \Illuminate\Support\Facades\Cache::put($cacheKey, $bellRows, now()->addSeconds(20));
    }

    $recentNotifications = collect($bellRows)->map(function ($row) {
        return (object) [
            'id' => $row['id'],
            'title' => $row['title'],
            'body' => $row['body'],
            'link' => $row['link'],
            'read_at' => ! empty($row['read_at']) ? \Illuminate\Support\Carbon::parse($row['read_at']) : null,
            'created_at' => ! empty($row['created_at']) ? \Illuminate\Support\Carbon::parse($row['created_at']) : null,
        ];
    });
    $unreadNotifications = $recentNotifications->whereNull('read_at')->count();
@endphp

<div class="relative" data-notif-menu>
    <button type="button"
            data-notif-toggle
            class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl border border-ink/10 bg-white text-ink transition hover:border-brand/40 hover:bg-brand-mist hover:text-brand-deeper"
            aria-expanded="false"
            aria-label="Notifikasi{{ $unreadNotifications ? ' ('.$unreadNotifications.' belum dibaca)' : '' }}">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        @if ($unreadNotifications > 0)
            <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-[#27CCF5] px-1 text-[10px] font-bold text-[#0B1F2A] ring-2 ring-white">
                {{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}
            </span>
        @endif
    </button>

    <div class="absolute right-0 z-50 mt-2 hidden w-[min(22rem,calc(100vw-2rem))] overflow-hidden rounded-2xl border border-brand/15 bg-white shadow-xl" data-notif-panel>
        <div class="flex items-center justify-between border-b border-ink/8 px-4 py-3">
            <p class="font-display text-sm font-semibold text-ink">Notifikasi</p>
            @if ($unreadNotifications > 0)
                <span class="rounded-full bg-brand/15 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-brand-deeper">
                    {{ $unreadNotifications }} baru
                </span>
            @endif
        </div>

        <div class="max-h-80 overflow-y-auto">
            @forelse ($recentNotifications as $notification)
                @php
                    $target = $notification->link ?: route('notifications.index');
                @endphp
                @if (! $notification->read_at)
                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                        @csrf
                        <button type="submit" class="flex w-full gap-3 border-b border-ink/5 px-4 py-3 text-left transition hover:bg-brand/5 bg-brand/[0.04]">
                            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-brand"></span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-semibold text-ink">{{ $notification->title }}</span>
                                <span class="mt-0.5 line-clamp-2 block text-xs text-ink-soft">{{ $notification->body }}</span>
                                <span class="mt-1 block text-[11px] text-ink-soft">{{ $notification->created_at?->diffForHumans() }}</span>
                            </span>
                        </button>
                    </form>
                @else
                    <a href="{{ $target }}" class="flex gap-3 border-b border-ink/5 px-4 py-3 transition hover:bg-brand/5">
                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-slate-300"></span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-medium text-ink">{{ $notification->title }}</span>
                            <span class="mt-0.5 line-clamp-2 block text-xs text-ink-soft">{{ $notification->body }}</span>
                            <span class="mt-1 block text-[11px] text-ink-soft">{{ $notification->created_at?->diffForHumans() }}</span>
                        </span>
                    </a>
                @endif
            @empty
                <div class="px-4 py-8 text-center">
                    <p class="text-sm font-medium text-ink">Belum ada notifikasi</p>
                    <p class="mt-1 text-xs text-ink-soft">Update kelas & pembayaran akan muncul di sini.</p>
                </div>
            @endforelse
        </div>

        <div class="border-t border-ink/8 p-2">
            <a href="{{ route('notifications.index') }}" class="block rounded-xl px-3 py-2 text-center text-sm font-semibold text-brand-mid transition hover:bg-brand/10">
                Lihat semua notifikasi
            </a>
        </div>
    </div>
</div>
@endauth
