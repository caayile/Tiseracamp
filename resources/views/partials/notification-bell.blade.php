@auth
@php
    $bellPayload = notification_bell_payload();
    $recentNotifications = collect($bellPayload['rows'])->map(function ($row) {
        return (object) [
            'id' => $row['id'],
            'title' => $row['title'],
            'body' => $row['body'],
            'link' => $row['link'],
            'read_at' => ! empty($row['read_at']) ? \Illuminate\Support\Carbon::parse($row['read_at']) : null,
            'created_at' => ! empty($row['created_at']) ? \Illuminate\Support\Carbon::parse($row['created_at']) : null,
        ];
    });
    $unreadNotifications = (int) ($bellPayload['unread'] ?? 0);
@endphp

<div class="relative" data-notif-menu>
    <button type="button"
            data-notif-toggle
            class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl border border-ink/10 bg-panel text-ink transition hover:border-brand/40 hover:bg-brand-mist hover:text-brand-deeper"
            aria-expanded="false"
            aria-label="Notifikasi{{ $unreadNotifications ? ' ('.$unreadNotifications.' belum dibaca)' : '' }}">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        @if ($unreadNotifications > 0)
            <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-[#27CCF5] px-1 text-[10px] font-bold text-[#0B1F2A] ring-2 ring-panel">
                {{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}
            </span>
        @endif
    </button>

    <div class="absolute right-0 z-50 mt-2 hidden overflow-hidden rounded-2xl border border-brand/15 bg-panel shadow-xl" data-notif-panel>
        <div class="flex shrink-0 items-center justify-between gap-2 border-b border-ink/8 px-3 py-2">
            <p class="font-display text-sm font-semibold text-ink">Notifikasi</p>
            <div class="flex items-center gap-2">
                @if ($unreadNotifications > 0)
                    <span class="rounded-full bg-brand/15 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-brand-deeper">
                        {{ $unreadNotifications }} baru
                    </span>
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        <button type="submit" class="text-[11px] font-semibold text-brand-mid hover:underline">Tandai semua</button>
                    </form>
                @endif
            </div>
        </div>

        <div data-notif-list>
            @forelse ($recentNotifications as $notification)
                <a href="{{ route('notifications.open', $notification->id) }}"
                   class="flex gap-2 border-b border-ink/5 px-3 py-2 transition hover:bg-brand/5 {{ $notification->read_at ? '' : 'bg-brand/[0.06]' }}">
                    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $notification->read_at ? 'bg-ink/20' : 'bg-brand' }}"></span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm {{ $notification->read_at ? 'font-medium text-ink' : 'font-semibold text-ink' }}">{{ $notification->title }}</span>
                        @if (filled($notification->body))
                            <span class="mt-0.5 block truncate text-xs text-ink-soft">{{ $notification->body }}</span>
                        @endif
                        <span class="mt-0.5 block text-[10px] text-ink-soft">{{ $notification->created_at?->diffForHumans() }}</span>
                    </span>
                </a>
            @empty
                <div class="px-3 py-10 text-center">
                    <p class="text-sm font-medium text-ink">Belum ada notifikasi</p>
                </div>
            @endforelse
        </div>

        <div class="shrink-0 border-t border-ink/8 p-1.5">
            <a href="{{ route('notifications.index') }}" class="block rounded-xl px-3 py-1.5 text-center text-sm font-semibold text-brand-mid transition hover:bg-brand/10">
                Lihat semua
            </a>
        </div>
    </div>
</div>
@endauth
