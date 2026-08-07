@extends('layouts.admin')

@section('title', 'Rekening Pembayaran')
@section('heading', 'Rekening Pembayaran')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <div class="rounded-2xl border border-brand/20 bg-brand-mist/40 px-5 py-4 text-sm text-ink-soft">
        Nomor rekening ini ditampilkan di checkout bootcamp/program dan paket Review CV AI.
        Siswa akan transfer ke rekening yang sedang aktif di sini.
    </div>

    <form method="POST" action="{{ route('admin.payment-account.update') }}" class="card-soft space-y-4 p-6">
        @csrf
        @method('PUT')

        <div>
            <label class="mb-1.5 block text-sm font-medium text-ink">Bank</label>
            <input type="text" name="bank_name" value="{{ old('bank_name', $account->bank_name) }}"
                   class="input-field" placeholder="Contoh: BCA / Mandiri / BNI" required maxlength="80">
            @error('bank_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-medium text-ink">Nomor rekening</label>
            <input type="text" name="account_number" value="{{ old('account_number', $account->account_number) }}"
                   class="input-field font-mono tracking-wide" placeholder="Contoh: 1234567890" required maxlength="64" inputmode="numeric">
            @error('account_number') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-medium text-ink">Atas nama</label>
            <input type="text" name="account_holder" value="{{ old('account_holder', $account->account_holder) }}"
                   class="input-field" placeholder="Contoh: PT Tiga Serangkai" required maxlength="160">
            @error('account_holder') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="rounded-xl border border-ink/10 bg-surface p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-ink-soft">Pratinjau checkout</p>
            <p class="mt-2 text-xs font-semibold uppercase tracking-wide text-brand-mid" id="preview-bank">{{ $account->bank_name }}</p>
            <p class="mt-1 font-display text-2xl font-bold tracking-wide text-ink" id="preview-number">{{ $account->account_number }}</p>
            <p class="mt-1 text-sm text-ink-soft" id="preview-holder">a.n. {{ $account->account_holder }}</p>
        </div>

        <div class="flex flex-wrap gap-3 pt-2">
            <button type="submit" class="btn-primary">Simpan rekening</button>
            <a href="{{ route('admin.payments.index') }}" class="btn-secondary">Kembali ke pembayaran</a>
        </div>
    </form>
</div>

<script>
(() => {
    const bank = document.querySelector('input[name="bank_name"]');
    const number = document.querySelector('input[name="account_number"]');
    const holder = document.querySelector('input[name="account_holder"]');
    const sync = () => {
        document.getElementById('preview-bank').textContent = bank.value || '—';
        document.getElementById('preview-number').textContent = number.value || '—';
        document.getElementById('preview-holder').textContent = 'a.n. ' + (holder.value || '—');
    };
    [bank, number, holder].forEach((el) => el?.addEventListener('input', sync));
})();
</script>
@endsection
