@extends('layouts.admin')

@php
    $statusOptions = [
        'submitted' => 'Menunggu seleksi',
        'under_review' => 'Sedang ditinjau',
        'accepted' => 'Diterima',
        'rejected' => 'Ditolak',
    ];
    $formFields = [
        'Nama lengkap' => $application->displayName(),
        'No. telepon / WhatsApp' => $application->phone,
        'Email' => $application->user?->email,
        'Jenjang' => $application->education_level,
        'Semester / tingkat' => $application->semester,
        'Universitas / sekolah' => $application->university,
        'Jurusan' => $application->major,
        'Link portfolio' => $application->portfolio_url,
    ];
    if (filled(trim((string) $application->motivation))) {
        $formFields['Motivasi'] = $application->motivation;
    }
    if (filled(trim((string) $application->experience))) {
        $formFields['Pengalaman'] = $application->experience;
    }
@endphp

@section('title', 'Form Pendaftaran')
@section('heading', 'Form Pendaftaran')

@section('content')
<div class="mb-6 flex flex-wrap items-start justify-between gap-4">
    <div class="space-y-1 text-sm">
        <p>
            <span class="font-semibold text-ink">Pendaftar:</span>
            <span class="text-ink-soft">{{ $application->displayName() }}</span>
        </p>
        <p>
            <span class="font-semibold text-ink">Lowongan:</span>
            <span class="text-ink-soft">{{ $application->program?->title ?? '—' }}</span>
        </p>
    </div>
    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('admin.applications.pendaftar') }}"
       class="inline-flex items-center gap-2 rounded-xl border border-ink/20 bg-panel px-4 py-2.5 text-sm font-semibold text-ink transition hover:bg-brand-mist">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Kembali
    </a>
</div>

<div class="card-soft p-5 sm:p-6">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-bold {{ $application->statusColor() }}">{{ $application->statusLabel() }}</span>
        <form method="POST" action="{{ route('admin.applications.review', $application) }}">
            @csrf
            <select name="status" class="input-field min-w-[11.5rem] text-xs font-semibold {{ $application->statusColor() }}"
                    onchange="if (this.value === '{{ $application->status }}') return; this.form.submit();">
                @foreach ($statusOptions as $value => $label)
                    <option value="{{ $value }}" @selected($application->status === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        @foreach ($formFields as $label => $value)
            @php
                $display = is_string($value) ? trim($value) : $value;
                $isEmpty = $display === null || $display === '';
            @endphp
            <div class="{{ in_array($label, ['Motivasi', 'Pengalaman'], true) ? 'sm:col-span-2' : '' }}">
                <p class="text-[11px] font-bold uppercase tracking-wide text-ink-soft">{{ $label }}</p>
                @if ($label === 'Link portfolio' && filled($application->portfolio_url))
                    <a href="{{ $application->portfolio_url }}" target="_blank" rel="noopener" class="mt-1 break-all text-sm font-medium text-brand-mid hover:underline">{{ $application->portfolio_url }}</a>
                @elseif ($isEmpty)
                    <p class="mt-1 text-sm text-ink-soft">—</p>
                @else
                    <p class="mt-1 whitespace-pre-line text-sm text-ink">{{ $display }}</p>
                @endif
            </div>
        @endforeach
        <div>
            <p class="text-[11px] font-bold uppercase tracking-wide text-ink-soft">Tanggal kirim</p>
            <p class="mt-1 text-sm text-ink">{{ $application->submitted_at?->translatedFormat('d M Y, H:i') ?? $application->created_at?->translatedFormat('d M Y, H:i') ?? '—' }}</p>
        </div>
        <div>
            <p class="text-[11px] font-bold uppercase tracking-wide text-ink-soft">Berkas diunggah</p>
            <p class="mt-1 text-sm text-ink">
                {{ collect(['CV' => $application->cv_path, 'Transkrip' => $application->transcript_path, 'Surat pengantar' => $application->cover_letter_path, 'Portfolio PDF' => $application->portfolio_path])->filter()->keys()->implode(', ') ?: '—' }}
            </p>
        </div>
        @if ($application->reviewer_note)
            <div class="sm:col-span-2">
                <p class="text-[11px] font-bold uppercase tracking-wide text-ink-soft">Catatan reviewer</p>
                <p class="mt-1 whitespace-pre-line text-sm text-ink">{{ $application->reviewer_note }}</p>
            </div>
        @endif
    </div>

    <div class="mt-6 border-t border-ink/8 pt-5">
        <p class="mb-3 text-[11px] font-bold uppercase tracking-wide text-ink-soft">Berkas persyaratan</p>
        <div class="flex flex-wrap gap-2">
            @forelse ($application->documents() as $doc)
                <a href="{{ $doc['url'] }}" target="_blank" rel="noopener"
                   class="inline-flex rounded-lg border border-ink/10 bg-panel px-3 py-1.5 text-xs font-semibold text-brand-mid transition hover:border-brand/40 hover:bg-brand-mist">
                    {{ $doc['label'] }}
                </a>
            @empty
                <span class="text-sm text-ink-soft">Tidak ada berkas</span>
            @endforelse
        </div>
    </div>
</div>
@endsection
