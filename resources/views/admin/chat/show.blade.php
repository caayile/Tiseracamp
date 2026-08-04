@extends('layouts.admin')

@section('title', 'Chat — '.$conversation->student->name)
@section('heading', 'Chat: '.$conversation->student->name)

@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div>
        <a href="{{ route('admin.chat.index') }}" class="btn-secondary text-sm">← Semua chat</a>
        <p class="mt-2 text-sm text-ink-soft">{{ $conversation->program?->title }} · {{ $conversation->student->email }}</p>
    </div>
</div>

<div class="card-soft flex flex-col overflow-hidden" style="min-height: 28rem;">
    <div class="flex-1 space-y-3 overflow-y-auto bg-surface p-4" style="max-height: 28rem;">
        @forelse ($conversation->messages as $message)
            @php $mine = $message->user_id === auth()->id(); @endphp
            <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[80%] rounded-2xl px-4 py-2.5 text-sm {{ $mine ? 'rounded-br-md bg-ink text-white' : 'rounded-bl-md border border-brand/15 bg-panel text-ink shadow-sm' }}">
                    <p class="text-[11px] font-semibold {{ $mine ? 'text-white/60' : 'text-brand-mid' }}">
                        {{ $mine ? 'Kamu (Admin)' : $message->user->name }}
                    </p>
                    <p class="mt-0.5 whitespace-pre-line">{{ $message->body }}</p>
                    <p class="mt-1 text-[10px] opacity-60">{{ $message->created_at->format('d M, H:i') }}</p>
                </div>
            </div>
        @empty
            <p class="py-8 text-center text-sm text-ink-soft">Belum ada pesan.</p>
        @endforelse
    </div>

    <form method="POST" action="{{ route('admin.chat.send', $conversation) }}" class="border-t border-brand/10 p-4">
        @csrf
        <div class="flex gap-2">
            <textarea name="body" rows="2" class="input-field flex-1 resize-none" placeholder="Kirim pesan / link Meet ke siswa..." required></textarea>
            <button class="btn-primary shrink-0 self-end" type="submit">Kirim</button>
        </div>
    </form>
</div>
@endsection
