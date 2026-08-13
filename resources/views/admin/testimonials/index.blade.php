@extends('layouts.admin')

@section('title', 'Testimoni')
@section('heading', 'Moderasi Testimoni')

@section('content')
<div class="space-y-4">
    @forelse ($testimonials as $testimonial)
        <div class="card-soft p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="font-semibold text-ink">{{ $testimonial->user?->name }}</p>
                    <p class="text-xs text-ink-soft">{{ $testimonial->program?->title }} · {{ $testimonial->created_at->diffForHumans() }}</p>
                    <p class="mt-3 text-sm text-ink-soft whitespace-pre-line">{{ $testimonial->body }}</p>
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $testimonial->is_published ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                    {{ $testimonial->is_published ? 'Tayang' : 'Menunggu' }}
                </span>
            </div>
            <div class="mt-4 flex gap-2">
                <form method="POST" action="{{ route('admin.testimonials.publish', $testimonial) }}">
                    @csrf
                    <button class="btn-secondary text-xs" type="submit">{{ $testimonial->is_published ? 'Sembunyikan' : 'Publikasikan' }}</button>
                </form>
                <form method="POST" action="{{ route('admin.testimonials.destroy', $testimonial) }}" onsubmit="return confirm('Hapus testimoni ini?')">
                    @csrf @method('DELETE')
                    <button class="btn-ghost text-xs text-red-600" type="submit">Hapus</button>
                </form>
            </div>
        </div>
    @empty
        <div class="card-soft p-10 text-center">
            <p class="font-display text-lg font-semibold">Belum ada testimoni</p>
        </div>
    @endforelse
    {{ $testimonials->links() }}
</div>
@endsection
