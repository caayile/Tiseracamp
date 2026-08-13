@extends(! empty($mentorMode) ? 'layouts.mentor' : 'layouts.app')

@section('title', $discussion->title)
@section('heading', 'Diskusi')

@section('content')
<section class="{{ ! empty($mentorMode) ? '' : 'mx-auto max-w-3xl px-4 py-10' }}">
    <x-back-nav :fallback="! empty($mentorMode) ? route('mentor.discussions.index') : route('learn.show', $discussion->program)" class="mb-4" />

    <article class="card-soft mt-6 p-6">
        <h1 class="font-display text-2xl font-semibold">{{ $discussion->title }}</h1>
        <p class="mt-2 text-xs text-ink-soft">{{ $discussion->user->name }} · {{ $discussion->created_at->translatedFormat('d M Y, H:i') }}</p>
        <div class="mt-4 text-sm leading-relaxed text-ink whitespace-pre-line">{{ $discussion->body }}</div>
    </article>

    <div class="mt-6 space-y-4">
        <h2 class="font-display text-lg font-semibold">{{ $discussion->replies->count() }} balasan</h2>

        @foreach ($discussion->replies as $reply)
            <div class="card-soft p-4">
                <p class="text-sm font-semibold text-ink">{{ $reply->user->name }}</p>
                <p class="text-xs text-ink-soft">{{ $reply->created_at->diffForHumans() }}</p>
                <p class="mt-2 text-sm text-ink whitespace-pre-line">{{ $reply->body }}</p>
            </div>
        @endforeach
    </div>

    <form method="POST" action="{{ route('discussions.reply', $discussion) }}" class="card-soft mt-6 space-y-3 p-6">
        @csrf
        <label class="text-sm font-medium">Tulis balasan</label>
        <textarea name="body" rows="4" class="input-field" placeholder="Bagikan pendapat atau jawaban..." required></textarea>
        <button class="btn-primary" type="submit">Kirim balasan</button>
    </form>
</section>
@endsection
