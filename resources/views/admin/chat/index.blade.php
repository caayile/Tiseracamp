@extends('layouts.admin')

@section('title', 'Chat Magang')
@section('heading', 'Chat Siswa Magang')

@section('content')
<p class="mb-6 text-sm text-ink-soft">Percakapan dengan siswa yang diterima di lowongan magang. Sesi Meet juga dikirim ke chat ini otomatis.</p>

@if ($conversations->isEmpty())
    <div class="card-soft p-10 text-center">
        <p class="font-display text-lg font-semibold">Belum ada chat</p>
        <p class="mt-2 text-sm text-ink-soft">Chat muncul setelah ada siswa diterima, atau setelah kamu buat sesi Meet dengan opsi kirim via chat.</p>
    </div>
@else
    <div class="card-soft divide-y divide-brand/10 overflow-hidden">
        @foreach ($conversations as $conversation)
            @php $last = $conversation->messages->first(); @endphp
            <a href="{{ route('admin.chat.show', $conversation) }}" class="flex items-start justify-between gap-4 px-5 py-4 transition hover:bg-brand-mist/40">
                <div class="min-w-0">
                    <p class="font-semibold text-ink">{{ $conversation->student?->name }}</p>
                    <p class="text-xs text-brand-mid">{{ $conversation->program?->title }}</p>
                    <p class="mt-1 truncate text-sm text-ink-soft">{{ $last?->body ?? 'Belum ada pesan' }}</p>
                </div>
                @if ($last)
                    <span class="shrink-0 text-[11px] text-ink-soft">{{ $last->created_at->diffForHumans() }}</span>
                @endif
            </a>
        @endforeach
    </div>
@endif
@endsection
