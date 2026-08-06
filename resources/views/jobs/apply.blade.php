@extends('layouts.app')

@section('title', 'Lamar — '.$program->title)

@section('content')
<section class="mx-auto max-w-2xl px-4 py-10">
    <x-back-nav :fallback="route('programs.show', $program->slug)" class="mb-6" />
    <h1 class="font-display text-2xl font-bold text-ink">Lamar Lowongan</h1>
    <p class="mt-1 text-sm text-ink-soft">{{ $program->title }}@if($program->partner) · {{ $program->partner->name }}@endif</p>

    <form method="POST" action="{{ route('jobs.store', $program) }}" enctype="multipart/form-data" class="card-soft mt-6 space-y-4 p-6">
        @csrf
        <div>
            <label class="mb-1.5 block text-sm font-semibold text-ink">Nama lengkap</label>
            <input type="text" name="full_name" value="{{ old('full_name', $application->full_name ?? $user->name) }}" class="input-field" required>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-ink">Telepon</label>
                <input type="text" name="phone" value="{{ old('phone', $application->phone ?? $user->phone) }}" class="input-field" required>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-ink">Email</label>
                <input type="email" name="email" value="{{ old('email', $application->email ?? $user->email) }}" class="input-field" required>
            </div>
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-semibold text-ink">Motivasi singkat</label>
            <textarea name="motivation" rows="4" class="input-field">{{ old('motivation', $application->motivation ?? '') }}</textarea>
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-semibold text-ink">Link portofolio (opsional)</label>
            <input type="url" name="portfolio_url" value="{{ old('portfolio_url', $application->portfolio_url ?? '') }}" class="input-field" placeholder="https://...">
        </div>

        @if ($savedCv?->portfolio_file_url)
            <label class="flex items-start gap-2 rounded-xl border border-brand/15 bg-brand-mist/40 px-3 py-3 text-sm">
                <input type="checkbox" name="use_saved_cv" value="1" class="mt-0.5 rounded border-slate-300 text-brand focus:ring-brand" @checked(old('use_saved_cv', true))>
                <span>Pakai CV tersimpan: <strong>{{ $savedCv->title }}</strong></span>
            </label>
        @endif

        <div>
            <label class="mb-1.5 block text-sm font-semibold text-ink">Upload CV (PDF)</label>
            <input type="file" name="cv" accept=".pdf,application/pdf" class="input-field file:mr-3 file:rounded-lg file:border-0 file:bg-brand/20 file:px-3 file:py-1.5 file:text-xs file:font-semibold">
            @error('cv') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="btn-primary w-full justify-center">Kirim lamaran</button>
    </form>
</section>
@endsection
