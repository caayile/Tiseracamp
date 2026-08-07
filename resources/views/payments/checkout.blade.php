@extends('layouts.app')

@section('title', 'Checkout — '.$program->title)

@section('content')
<section class="mesh-bg border-b border-brand/10">
    <div class="mx-auto max-w-5xl px-4 py-10 sm:py-14">
        <x-back-nav :fallback="route('programs.show', $program->slug)" />

        <div class="mt-4 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-brand-dark">Checkout pembayaran</p>
                <h1 class="mt-2 font-display text-3xl font-bold text-ink sm:text-4xl">Selesaikan pendaftaran</h1>
                <p class="mt-2 max-w-xl text-sm text-ink-soft">Transfer sesuai nominal, lalu upload bukti — akses kelas dibuka setelah admin verifikasi.</p>
            </div>
        </div>

        {{-- Steps --}}
        <div class="mt-8 grid gap-3 sm:grid-cols-3">
            @foreach ([
                ['1', 'Pilih program', 'done'],
                ['2', 'Bayar & upload bukti', 'active'],
                ['3', 'Verifikasi admin', 'todo'],
            ] as [$num, $label, $state])
                <div class="flex items-center gap-3 rounded-2xl border px-4 py-3 {{ $state === 'active' ? 'border-brand/40 bg-brand-mist' : 'border-brand/15 bg-white' }}">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold {{ $state === 'active' ? 'bg-brand text-ink' : ($state === 'done' ? 'bg-emerald-100 text-emerald-700' : 'bg-surface text-ink-soft') }}">{{ $num }}</span>
                    <span class="text-sm font-semibold {{ $state === 'todo' ? 'text-ink-soft' : 'text-ink' }}">{{ $label }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="bg-surface py-10 sm:py-12">
    <div class="mx-auto grid max-w-5xl gap-6 px-4 lg:grid-cols-[1.05fr_0.95fr]">
        {{-- Order summary --}}
        <div class="overflow-hidden rounded-3xl border border-brand/15 bg-white shadow-sm">
            <div class="relative h-36 overflow-hidden bg-gradient-to-br from-brand-mist via-white to-brand-light/50">
                <div class="relative z-10 flex h-full flex-col justify-end p-6">
                    <span class="w-fit rounded-lg bg-brand px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-ink">{{ $program->typeLabel() }}</span>
                    <h2 class="mt-2 font-display text-xl font-bold text-ink">{{ $program->title }}</h2>
                </div>
            </div>

            <div class="space-y-4 p-6">
                <div class="flex flex-wrap gap-2 text-xs">
                    <span class="rounded-lg bg-brand-mist px-2.5 py-1 font-semibold text-brand-mid">{{ $program->level }}</span>
                    <span class="rounded-lg bg-surface px-2.5 py-1 font-medium text-ink-soft">{{ $program->formattedDuration() }}</span>
                    @if ($program->mentor)
                        <span class="rounded-lg bg-surface px-2.5 py-1 font-medium text-ink-soft">Mentor: {{ $program->mentor->name }}</span>
                    @endif
                </div>

                @if ($program->excerpt)
                    <p class="text-sm leading-relaxed text-ink-soft">{{ $program->excerpt }}</p>
                @endif

                <ul class="space-y-2">
                    @foreach (array_slice($program->benefits ?? ['Akses materi lengkap', 'Mentoring', 'Sertifikat digital'], 0, 4) as $benefit)
                        <li class="flex items-start gap-2 text-sm text-ink">
                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-brand/20 text-brand-mid">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            {{ $benefit }}
                        </li>
                    @endforeach
                </ul>

                <div class="rounded-2xl border border-brand/20 bg-brand-mist p-5">
                    <div class="flex items-end justify-between gap-3">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-ink-soft">Total pembayaran</p>
                            <p class="mt-1 font-display text-3xl font-bold text-brand-mid">{{ $program->formattedPrice() }}</p>
                        </div>
                        <p class="text-right text-[11px] text-ink-soft">Transfer exact<br>sesuai nominal</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Payment + upload --}}
        <div class="space-y-5">
            <div class="rounded-3xl border border-[#0B1F2A]/8 bg-white p-6 shadow-[0_20px_50px_-28px_rgba(11,31,42,0.35)]">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#0B9BC4]">Rekening tujuan</p>
                <h3 class="mt-1 font-display text-lg font-semibold text-[#0B1F2A]">Transfer bank</h3>

                @php $bank = payment_account(); @endphp
                <div class="mt-5 space-y-3">
                    <div class="rounded-2xl border border-[#27CCF5]/25 bg-gradient-to-br from-[#E8F9FE] to-white p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-[#065A7A]">{{ $bank['bank_name'] }}</p>
                                <p class="mt-1 font-display text-2xl font-bold tracking-wide text-[#0B1F2A]" data-copy-value="{{ $bank['account_number'] }}">{{ $bank['account_number'] }}</p>
                                <p class="mt-1 text-sm text-slate-500">a.n. {{ $bank['account_holder'] }}</p>
                            </div>
                            <button type="button" data-copy="{{ $bank['account_number'] }}" class="rounded-xl bg-brand px-3 py-2 text-xs font-semibold text-ink transition hover:bg-brand-light">Salin</button>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                            <p class="text-[11px] font-semibold uppercase text-slate-400">Jumlah</p>
                            <p class="mt-1 text-sm font-bold text-[#0B1F2A]">{{ $program->formattedPrice() }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                            <p class="text-[11px] font-semibold uppercase text-slate-400">Estimasi</p>
                            <p class="mt-1 text-sm font-bold text-[#0B1F2A]">Verifikasi 1×24 jam</p>
                        </div>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('payments.store', $program) }}" enctype="multipart/form-data" class="rounded-3xl border border-[#0B1F2A]/8 bg-white p-6 shadow-[0_20px_50px_-28px_rgba(11,31,42,0.35)]" data-upload-form>
                @csrf
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#0B9BC4]">Langkah terakhir</p>
                <h3 class="mt-1 font-display text-lg font-semibold text-[#0B1F2A]">Upload bukti transfer</h3>
                <p class="mt-1 text-sm text-slate-500">JPG, PNG, atau PDF · maks. 5MB</p>

                <label data-dropzone class="mt-5 flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-[#27CCF5]/40 bg-[#E8F9FE]/50 px-4 py-10 text-center transition hover:border-[#27CCF5] hover:bg-[#27CCF5]/10">
                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-mist text-brand-mid">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    </span>
                    <span class="mt-3 text-sm font-semibold text-[#0B1F2A]">Klik atau drop file di sini</span>
                    <span data-file-name class="mt-1 text-xs text-slate-500">Belum ada file dipilih</span>
                    <input type="file" name="proof" accept=".jpg,.jpeg,.png,.pdf" class="sr-only" required data-file-input>
                </label>
                @error('proof') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror

                <div class="mt-4 rounded-xl bg-amber-50 px-4 py-3 text-xs leading-relaxed text-amber-800">
                    Pastikan nominal transfer <strong>tepat</strong> dan nama pengirim terbaca jelas di bukti.
                </div>

                <button class="mt-5 w-full rounded-2xl bg-[#27CCF5] py-3.5 text-sm font-bold text-[#0B1F2A] shadow-[0_14px_30px_-12px_rgba(39,204,245,0.8)] transition hover:-translate-y-0.5 hover:bg-[#7DE6FA]" type="submit">
                    Kirim bukti pembayaran
                </button>
            </form>
        </div>
    </div>
</section>

<script>
document.querySelectorAll('[data-copy]').forEach((btn) => {
    btn.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(btn.dataset.copy);
            const prev = btn.textContent;
            btn.textContent = 'Tersalin!';
            setTimeout(() => (btn.textContent = prev), 1500);
        } catch (e) {}
    });
});

const input = document.querySelector('[data-file-input]');
const nameEl = document.querySelector('[data-file-name]');
const zone = document.querySelector('[data-dropzone]');
input?.addEventListener('change', () => {
    nameEl.textContent = input.files?.[0]?.name || 'Belum ada file dipilih';
});
['dragenter', 'dragover'].forEach((ev) => {
    zone?.addEventListener(ev, (e) => {
        e.preventDefault();
        zone.classList.add('border-[#27CCF5]', 'bg-[#27CCF5]/15');
    });
});
['dragleave', 'drop'].forEach((ev) => {
    zone?.addEventListener(ev, (e) => {
        e.preventDefault();
        zone.classList.remove('border-[#27CCF5]', 'bg-[#27CCF5]/15');
    });
});
zone?.addEventListener('drop', (e) => {
    if (e.dataTransfer.files?.length) {
        input.files = e.dataTransfer.files;
        nameEl.textContent = e.dataTransfer.files[0].name;
    }
});
</script>
@endsection
