@props([
    'steps' => [],
    'stepLinks' => [],
    'activeStep' => null,
])

<div class="relative">
    @foreach ($steps as $step => $info)
        @php
            $isLast = $loop->last;
            $state = $info['state'];
            $detail = $info['detail'] ?? null;
            $link = $stepLinks[$step] ?? null;
            $nextState = $steps[$step + 1]['state'] ?? 'pending';
            $connectorClass = match (true) {
                $state === 'completed' && $nextState === 'rejected' => 'bg-red-300',
                $state === 'completed' => 'bg-emerald-400',
                default => 'bg-ink/10',
            };
        @endphp
        <div class="relative {{ $isLast ? '' : 'pb-8' }}">
            {{-- Step Connector --}}
            @if (! $isLast)
                <div class="absolute left-[14px] top-8 bottom-0 w-[3px] rounded-full {{ $connectorClass }}"></div>
            @endif

            {{-- Baris step --}}
            <div class="relative flex items-start gap-4 {{ $link ? 'cursor-pointer' : '' }}">
                @if ($link)<a href="{{ $link }}" class="absolute inset-0 z-20" aria-label="{{ $info['label'] }}"></a>@endif

                {{-- Circle indicator --}}
                <div class="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border-2
                    {{ match($state) {
                        'completed' => 'border-emerald-500 bg-emerald-500 text-white',
                        'active' => 'border-brand bg-white text-brand',
                        'rejected' => 'border-red-400 bg-red-50 text-red-500',
                        default => 'border-ink/10 bg-ink/5 text-ink-soft/40',
                    } }}">
                    @if ($state === 'completed')
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    @elseif ($state === 'rejected')
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    @else
                        <span class="text-xs font-bold">{{ $step }}</span>
                    @endif
                </div>

                {{-- Content --}}
                <div class="min-w-0 flex-1 pt-0.5 {{ $state === 'pending' ? 'opacity-60' : '' }}">
                    <p class="text-sm font-semibold {{ match($state) {
                        'completed' => 'text-ink',
                        'active' => 'text-brand-deeper',
                        'rejected' => 'text-red-600',
                        default => 'text-ink-soft/50',
                    } }}">{{ $info['label'] }}</p>
                    <p class="mt-0.5 text-xs {{ match($state) {
                        'completed' => 'text-ink-soft',
                        'active' => 'text-ink',
                        'rejected' => 'text-red-500',
                        default => 'text-ink-soft/40',
                    } }}">{{ $info['description'] }}</p>

                    @if ($detail)
                        <p class="mt-1.5 text-xs leading-relaxed text-ink-soft/70">{{ $detail }}</p>
                    @endif
                </div>

                {{-- Panah untuk step yang bisa dibuka --}}
                @if ($link)
                    <svg class="mt-1.5 h-4 w-4 shrink-0 text-ink-soft/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                @endif
            </div>
        </div>
    @endforeach
</div>
