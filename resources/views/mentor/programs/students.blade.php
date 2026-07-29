@extends('layouts.mentor')

@section('title', 'Siswa — '.$program->title)
@section('heading', 'Siswa: '.$program->title)

@section('content')
<div class="mb-6">
    <a href="{{ route('mentor.programs.index') }}" class="btn-secondary">← Kembali</a>
</div>

<div class="card-soft overflow-hidden">
    <table class="min-w-full text-left text-sm">
        <thead class="bg-brand-mist/60 text-ink-soft">
            <tr>
                <th class="px-5 py-3 font-medium">Nama</th>
                <th class="px-5 py-3 font-medium">Email</th>
                <th class="px-5 py-3 font-medium">Progress</th>
                <th class="px-5 py-3 font-medium">Status</th>
                <th class="px-5 py-3 font-medium">Daftar</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($enrollments as $enrollment)
                <tr class="border-t border-brand/10">
                    <td class="px-5 py-3 font-medium">{{ $enrollment->user->name }}</td>
                    <td class="px-5 py-3">{{ $enrollment->user->email }}</td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="progress-bar w-20"><span style="width: {{ $enrollment->progress }}%"></span></div>
                            <span>{{ $enrollment->progress }}%</span>
                        </div>
                    </td>
                    <td class="px-5 py-3">{{ ucfirst($enrollment->status) }}</td>
                    <td class="px-5 py-3 text-ink-soft">{{ $enrollment->enrolled_at?->translatedFormat('d M Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-5 py-8 text-center text-ink-soft">Belum ada siswa terdaftar.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
