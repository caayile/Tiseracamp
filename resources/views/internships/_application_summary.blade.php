<p class="text-[11px] font-semibold uppercase tracking-wide text-ink-soft">Data pendaftaran kamu</p>

<dl class="mt-3 grid grid-cols-1 gap-x-6 gap-y-2.5 sm:grid-cols-2">
    @foreach ([
        'Nama lengkap' => $application->displayName(),
        'No. telepon / WhatsApp' => $application->phone,
        'Jenjang' => $application->education_level,
        'Semester / tingkat' => $application->semester,
        'Universitas / sekolah' => $application->university,
        'Jurusan' => $application->major,
    ] as $label => $value)
        <div>
            <dt class="text-[11px] uppercase tracking-wide text-ink-soft/70">{{ $label }}</dt>
            <dd class="mt-0.5 text-sm font-medium text-ink">{{ filled($value) ? $value : '—' }}</dd>
        </div>
    @endforeach
</dl>

@php
    $docs = collect([
        ['label' => 'CV', 'url' => $application->publicDocumentUrl('cv')],
        ['label' => 'Portfolio', 'url' => $application->publicDocumentUrl('portfolio')],
    ])->filter(fn ($doc) => filled($doc['url']));
@endphp

@if ($docs->isNotEmpty() || filled($application->portfolio_url))
    <p class="mt-4 text-[11px] font-semibold uppercase tracking-wide text-ink-soft">Dokumen terlampir</p>
    <div class="mt-2 flex flex-wrap gap-2">
        @foreach ($docs as $doc)
            <a href="{{ $doc['url'] }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 rounded-full bg-brand/15 px-3 py-1 text-xs font-semibold text-brand-dark transition hover:bg-brand/25">
                {{ $doc['label'] }}
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        @endforeach
        @if (filled($application->portfolio_url))
            <a href="{{ $application->portfolio_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 rounded-full bg-brand/15 px-3 py-1 text-xs font-semibold text-brand-dark transition hover:bg-brand/25">
                Link portfolio
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        @endif
    </div>
@endif

<p class="mt-3.5 text-[11px] italic text-ink-soft/60">Data hanya dapat dilihat setelah dikirim.</p>
