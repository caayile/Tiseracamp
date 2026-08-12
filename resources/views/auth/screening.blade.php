@extends('layouts.app')

@section('title', 'Onboarding — Lengkapi Akun')

@section('content')
<section class="mesh-bg border-b border-brand/10">
    <div class="mx-auto max-w-2xl px-4 py-10">
        <x-back-nav :fallback="route('home')" class="mb-4" />
        <span class="badge">Onboarding</span>
        <h1 class="section-title mt-3">Selamat datang, {{ auth()->user()->name }}!</h1>
        <p class="mt-2 text-ink-soft">Selesaikan beberapa langkah singkat supaya akunmu siap dipakai. Hanya butuh sekitar 1 menit.</p>

        <div class="mt-6">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wide text-ink-soft" data-progress-label>Langkah 1 dari 3</p>
            </div>
            <div class="mt-2 flex gap-2">
                <span class="h-1.5 flex-1 rounded-full bg-brand transition" data-progress-dot="1"></span>
                <span class="h-1.5 flex-1 rounded-full bg-ink/10 transition" data-progress-dot="2"></span>
                <span class="h-1.5 flex-1 rounded-full bg-ink/10 transition" data-progress-dot="3"></span>
            </div>
        </div>
    </div>
</section>

<section class="mx-auto max-w-2xl px-4 py-10">
    <form method="POST" action="{{ route('screening.store') }}" enctype="multipart/form-data" id="onboarding-form" class="card-soft p-6 sm:p-8">
        @csrf

        {{-- Step 1: Intro --}}
        <div data-step="1" class="space-y-6">
            <div class="text-center">
                <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-brand to-brand-deeper text-white shadow-lg shadow-brand/30">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
                </span>
                <h2 class="mt-4 font-display text-2xl font-bold text-ink">Halo, {{ explode(' ', auth()->user()->name)[0] }}!</h2>
                <p class="mt-2 text-sm text-ink-soft">Kami ingin memastikan akunmu terverifikasi supaya kamu bisa mengikuti bootcamp, magang, hingga fitur khusus lainnya.</p>
            </div>
            <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-2xl border border-ink/10 bg-surface p-4 text-center">
                    <p class="font-display text-2xl font-bold text-brand-deeper">3</p>
                    <p class="mt-1 text-xs text-ink-soft">Langkah singkat</p>
                </div>
                <div class="rounded-2xl border border-ink/10 bg-surface p-4 text-center">
                    <p class="font-display text-2xl font-bold text-brand-deeper">1</p>
                    <p class="mt-1 text-xs text-ink-soft">Menit saja</p>
                </div>
                <div class="rounded-2xl border border-ink/10 bg-surface p-4 text-center">
                    <p class="font-display text-2xl font-bold text-brand-deeper">100%</p>
                    <p class="mt-1 text-xs text-ink-soft">Data terjaga</p>
                </div>
            </div>
            <button type="button" class="btn-primary w-full" data-next-step="2">
                Mulai
                <svg class="ml-1 inline h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
            </button>
        </div>

        {{-- Step 2: Status TSU --}}
        <div data-step="2" class="hidden space-y-6">
            <div>
                <h2 class="font-display text-xl font-bold text-ink">Apakah kamu mahasiswa Tiga Serangkai (TSU)?</h2>
                <p class="mt-1 text-sm text-ink-soft">Pilih status yang paling sesuai dengan kamu.</p>
            </div>

            <div class="grid gap-3">
                <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-ink/10 bg-surface p-4 transition has-[:checked]:border-brand has-[:checked]:bg-brand-mist has-[:checked]:ring-1 has-[:checked]:ring-brand/40">
                    <input type="radio" name="is_tsu" value="1" class="mt-0.5 h-5 w-5 shrink-0 accent-brand" data-tsu-trigger @checked(old('is_tsu') === '1')>
                    <span>
                        <span class="block font-semibold text-ink">Mahasiswa TSU</span>
                        <span class="mt-0.5 block text-xs text-ink-soft">Mahasiswa aktif Tiga Serangkai — dapat fasilitas khusus. Wajib unggah KTM.</span>
                    </span>
                </label>
                <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-ink/10 bg-surface p-4 transition has-[:checked]:border-brand has-[:checked]:bg-brand-mist has-[:checked]:ring-1 has-[:checked]:ring-brand/40">
                    <input type="radio" name="is_tsu" value="0" class="mt-0.5 h-5 w-5 shrink-0 accent-brand" data-tsu-trigger @checked(old('is_tsu') === '0')>
                    <span>
                        <span class="block font-semibold text-ink">Bukan dari TSU</span>
                        <span class="mt-0.5 block text-xs text-ink-soft">Mahasiswa universitas lain / umum — tidak perlu KTM.</span>
                    </span>
                </label>
            </div>
            @error('is_tsu') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            <p class="hidden text-xs text-red-600" data-tsu-error>Pilih salah satu dulu ya.</p>

            <div class="flex items-center justify-between gap-3">
                <button type="button" class="btn-secondary" data-prev-step="1">Kembali</button>
                <button type="button" class="btn-primary" data-tsu-next>
                    Lanjut
                    <svg class="ml-1 inline h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </button>
            </div>
        </div>

        {{-- Step 3: KTM upload (TSU only) --}}
        <div data-step="3" class="hidden space-y-6">
            <div>
                <h2 class="font-display text-xl font-bold text-ink">Unggah Kartu Tanda Mahasiswa</h2>
                <p class="mt-1 text-sm text-ink-soft">KTM diperlukan sebagai verifikasi mahasiswa TSU. Pastikan foto/scan terlihat jelas.</p>
            </div>

            <label class="flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-brand/30 bg-surface px-4 py-10 text-center transition hover:border-brand/60 hover:bg-brand-mist/30" for="ktm-input">
                <svg class="h-10 w-10 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                </svg>
                <p class="mt-3 text-sm font-semibold text-ink" data-ktm-label>Klik untuk memilih file</p>
                <p class="mt-1 text-xs text-ink-soft">PNG, JPG, atau PDF — maksimal 5 MB</p>
            </label>
            <input type="file" id="ktm-input" name="ktm" accept=".png,.jpg,.jpeg,.pdf" class="hidden" data-ktm-input>
            <p class="hidden text-xs text-red-600" data-ktm-error>File KTM wajib dipilih sebelum lanjut.</p>
            @error('ktm') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

            <div class="flex items-center justify-between gap-3">
                <button type="button" class="btn-secondary" data-prev-step="2">Kembali</button>
                <button type="button" class="btn-primary" data-ktm-next>
                    Lanjut
                    <svg class="ml-1 inline h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </button>
            </div>
        </div>

        {{-- Step 4: Selesai --}}
        <div data-step="4" class="hidden space-y-6 text-center">
            <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-brand/15 text-brand-deeper">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </span>
            <h2 class="font-display text-2xl font-bold text-ink">Semua siap!</h2>
            <p class="mx-auto max-w-md text-sm text-ink-soft" data-done-summary>
                Terima kasih! Akunmu sudah lengkap. Klik tombol di bawah untuk masuk ke dashboard.
            </p>

            <div class="flex flex-col gap-3">
                <button type="submit" class="btn-primary w-full">
                    Buka Dashboard
                    <svg class="ml-1 inline h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </button>
                <button type="button" class="btn-secondary" data-prev-step="3" data-prev-ktm>Kembali</button>
            </div>
        </div>
    </form>
</section>

<script>
(() => {
    const form = document.getElementById('onboarding-form');
    const steps = form.querySelectorAll('[data-step]');
    const progressLabel = document.querySelector('[data-progress-label]');
    const progressDots = document.querySelectorAll('[data-progress-dot]');

    const tsuInputs = form.querySelectorAll('[data-tsu-trigger]');
    const tsuError = form.querySelector('[data-tsu-error]');
    const ktmError = form.querySelector('[data-ktm-error]');
    const fileInput = form.querySelector('[data-ktm-input]');
    const fileLabel = form.querySelector('[data-ktm-label]');
    const doneSummary = form.querySelector('[data-done-summary]');

    let currentStep = 1;
    let currentMax = 3;

    const setProgress = (step) => {
        progressLabel.textContent = `Langkah ${step} dari 3`;
        progressDots.forEach((dot) => {
            dot.classList.toggle('bg-brand', +dot.dataset.progressDot <= step);
            dot.classList.toggle('bg-ink/10', +dot.dataset.progressDot > step);
        });
    };

    const goTo = (step) => {
        currentStep = step;
        steps.forEach((s) => s.classList.toggle('hidden', +s.dataset.step !== step));
        setProgress(step);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    const nextFromStatus = () => {
        const checked = form.querySelector('input[name="is_tsu"]:checked');
        if (!checked) {
            tsuError.classList.remove('hidden');
            return;
        }
        tsuError.classList.add('hidden');
        if (checked.value === '1') {
            goTo(3);
            currentMax = 3;
        } else {
            goTo(4);
            currentMax = 3;
        }
    };

    const nextFromKtm = () => {
        if (fileInput && !fileInput.files.length) {
            ktmError.classList.remove('hidden');
            return;
        }
        ktmError.classList.add('hidden');
        doneSummary.textContent = 'Terima kasih! KTM kamu sudah terlampir dan akunmu siap digunakan.';
        goTo(4);
    };

    form.querySelectorAll('[data-next-step]').forEach((btn) => {
        btn.addEventListener('click', () => goTo(+btn.dataset.nextStep));
    });
    form.querySelector('[data-tsu-next]')?.addEventListener('click', nextFromStatus);
    form.querySelector('[data-ktm-next]')?.addEventListener('click', nextFromKtm);

    form.querySelectorAll('[data-prev-step]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const target = +btn.dataset.prevStep;
            if (btn.dataset.prevKtm !== undefined) {
                const checked = form.querySelector('input[name="is_tsu"]:checked');
                goTo(checked && checked.value === '1' ? 3 : 2);
            } else {
                goTo(target);
            }
        });
    });

    tsuInputs.forEach((radio) => radio.addEventListener('change', () => tsuError.classList.add('hidden')));

    if (fileInput && fileLabel) {
        fileInput.addEventListener('change', () => {
            const file = fileInput.files[0];
            if (file) {
                fileLabel.textContent = file.name;
                fileLabel.closest('label').classList.add('border-brand');
            } else {
                fileLabel.textContent = 'Klik untuk memilih file';
            }
        });
    }

    const preselectedTsu = form.querySelector('input[name="is_tsu"]:checked');
    if (preselectedTsu && +preselectedTsu.value === 1) {
        goTo(3);
    }
})();
</script>
@endsection
