@extends('layouts.mentor')

@section('title', 'Diskusi')
@section('heading', 'Diskusi Siswa')

@section('content')
<div class="space-y-3">
    @forelse ($discussions as $discussion)
        <a href="{{ route('mentor.discussions.show', $discussion) }}" class="card-soft block p-5 hover:border-brand/30">
            <p class="font-semibold text-ink">{{ $discussion->title }}</p>
            <p class="mt-1 text-sm text-ink-soft">{{ $discussion->program?->title }} · {{ $discussion->user?->name }} · {{ $discussion->replies->count() }} balasan</p>
        </a>
    @empty
        <div class="card-soft p-10 text-center">
            <p class="font-display text-lg font-semibold">Belum ada diskusi</p>
        </div>
    @endforelse
</div>
<div class="mt-4">{{ $discussions->links() }}</div>
@endsection
