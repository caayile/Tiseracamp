@extends('layouts.admin')

@section('title', 'Seleksi Lowongan')
@section('heading', 'Seleksi Lamaran Kerja')

@section('content')
<div class="card-soft overflow-hidden">
    <table class="min-w-full text-left text-sm">
        <thead class="bg-brand-mist/60 text-ink-soft">
            <tr>
                <th class="px-5 py-3 font-medium">Pelamar</th>
                <th class="px-5 py-3 font-medium">Lowongan</th>
                <th class="px-5 py-3 font-medium">CV</th>
                <th class="px-5 py-3 font-medium">Status</th>
                <th class="px-5 py-3 font-medium">Review</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($applications as $application)
                <tr class="border-t border-brand/10 align-top">
                    <td class="px-5 py-3">
                        <p class="font-medium">
                            {{ $application->full_name }}
                            @if ($application->is_tsu)
                                <span class="badge ml-1 bg-brand/15 text-brand-dark ring-brand/30">Prioritas TSU</span>
                            @endif
                        </p>
                        <p class="text-xs text-ink-soft">{{ $application->email }} · {{ $application->phone }}</p>
                        @if ($application->motivation)
                            <p class="mt-1 text-xs text-ink-soft line-clamp-2">{{ $application->motivation }}</p>
                        @endif
                    </td>
                    <td class="px-5 py-3">{{ $application->program?->title }}</td>
                    <td class="px-5 py-3">
                        @if ($application->cv_path)
                            <a href="{{ media_url($application->cv_path) }}" target="_blank" class="text-xs text-brand-deeper underline">Lihat CV</a>
                        @else
                            —
                        @endif
                        @if ($application->portfolio_url)
                            <a href="{{ $application->portfolio_url }}" target="_blank" class="mt-1 block text-xs text-brand-deeper underline">Portofolio</a>
                        @endif
                    </td>
                    <td class="px-5 py-3"><span class="badge">{{ str_replace('_', ' ', $application->status) }}</span></td>
                    <td class="px-5 py-3">
                        <form method="POST" action="{{ route('admin.job-applications.review', $application) }}" class="space-y-2">
                            @csrf
                            <select name="status" class="input-field py-1 text-xs">
                                <option value="under_review" @selected($application->status === 'under_review')>Under review</option>
                                <option value="accepted" @selected($application->status === 'accepted')>Accepted</option>
                                <option value="rejected" @selected($application->status === 'rejected')>Rejected</option>
                            </select>
                            <input type="text" name="reviewer_note" value="{{ $application->reviewer_note }}" class="input-field py-1 text-xs" placeholder="Catatan">
                            <button class="btn-primary w-full text-xs" type="submit">Simpan</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-5 py-8 text-center text-ink-soft">Belum ada lamaran kerja.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-6">{{ $applications->links() }}</div>
@endsection
