@extends('layouts.app')

@section('title', 'Chat — '.$conversation->mentor->name)

@section('content')
<section class="mx-auto flex max-w-4xl flex-col px-4 py-6" style="height: calc(100vh - 8rem);">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <x-back-nav :fallback="route('chat.index')" />
            <h1 class="mt-1 font-display text-xl font-semibold">{{ $conversation->mentor->name }}</h1>
            <p class="text-xs text-ink-soft">Mentor · {{ $conversation->program->title }}</p>
        </div>
        <span class="rounded-xl bg-brand/15 px-3 py-1.5 text-[11px] font-bold uppercase tracking-wide text-brand-deeper">Mode Siswa</span>
    </div>

    <div class="card-soft flex flex-1 flex-col overflow-hidden">
        <div class="flex-1 space-y-4 overflow-y-auto bg-[#F8FCFD] p-4">
            @forelse ($conversation->messages as $message)
                @php $mine = $message->user_id === auth()->id(); @endphp
                <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[80%] rounded-2xl px-4 py-2.5 text-sm {{ $mine ? 'rounded-br-md bg-ink text-white' : 'rounded-bl-md border border-brand/15 bg-white text-ink shadow-sm' }}">
                        <p class="text-[11px] font-semibold {{ $mine ? 'text-white/60' : 'text-brand-deeper' }}">
                            {{ $mine ? 'Kamu' : 'Mentor · '.$message->user->name }}
                        </p>
                        <p class="mt-0.5 whitespace-pre-line">{{ $message->body }}</p>
                        <p class="mt-1 text-[10px] opacity-60">{{ $message->created_at->format('H:i') }}</p>
                    </div>
                </div>
            @empty
                <p class="text-center text-sm text-ink-soft">Belum ada pesan. Mulai percakapan dengan mentor!</p>
            @endforelse
        </div>

        <form method="POST" action="{{ route('chat.send', $conversation) }}" class="border-t border-brand/10 p-4">
            @csrf
            <div class="flex gap-2">
                <textarea name="body" rows="2" class="input-field flex-1 resize-none" placeholder="Tanya mentor..." required></textarea>
                <button class="btn-primary shrink-0 self-end" type="submit">Kirim</button>
            </div>
        </form>
    </div>
</section>
@endsection
