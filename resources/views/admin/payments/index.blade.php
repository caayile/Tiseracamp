@extends('layouts.admin')

@section('title', 'Pembayaran')
@section('heading', 'Verifikasi Pembayaran')

@section('content')
<div class="mb-8">
    @php $bank = payment_account(); @endphp
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-brand/20 bg-brand-mist/40 px-4 py-3">
        <div>
            <p class="text-sm font-semibold text-ink">Rekening tujuan transfer</p>
            <p class="text-xs text-ink-soft">
                {{ $bank['bank_name'] }} · {{ $bank['account_number'] }} · a.n. {{ $bank['account_holder'] }}
            </p>
        </div>
        <a href="{{ route('admin.payment-account.edit') }}" class="btn-secondary text-xs">Ubah rekening</a>
    </div>

    <div class="mb-3 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h2 class="font-display text-lg font-semibold text-ink">Paket Review CV AI</h2>
            <p class="text-sm text-ink-soft">Konfirmasi bukti transfer langganan Review CV AI. Gunakan tombol hijau untuk aktifkan.</p>
        </div>
        <a href="{{ route('admin.cv-subscriptions.index') }}" class="btn-ghost text-xs">Lihat semua paket CV</a>
    </div>

    <div class="card-soft overflow-hidden">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-brand-mist/60 text-ink-soft">
                <tr>
                    <th class="px-5 py-3 font-medium">Invoice</th>
                    <th class="px-5 py-3 font-medium">User</th>
                    <th class="px-5 py-3 font-medium">Paket</th>
                    <th class="px-5 py-3 font-medium">Nominal</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 font-medium">Verifikasi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($cvSubscriptions as $subscription)
                    <tr class="border-t border-brand/10 align-top {{ $subscription->status === 'waiting_verification' ? 'bg-amber-50/40' : '' }}">
                        <td class="px-5 py-3 font-medium">{{ $subscription->invoice_code }}</td>
                        <td class="px-5 py-3">
                            <p>{{ $subscription->user->name }}</p>
                            <p class="text-xs text-ink-soft">{{ $subscription->user->email }}</p>
                        </td>
                        <td class="px-5 py-3">
                            <p class="font-medium">{{ $subscription->plan_name }}</p>
                            <p class="text-xs text-ink-soft">
                                {{ $subscription->reviews_limit === null ? 'Unlimited' : $subscription->reviews_limit.'x review' }}
                            </p>
                        </td>
                        <td class="px-5 py-3">{{ $subscription->formattedAmount() }}</td>
                        <td class="px-5 py-3">
                            <span class="badge">{{ str_replace('_', ' ', $subscription->status) }}</span>
                            @if ($subscription->proof_path)
                                <a href="{{ media_url($subscription->proof_path) }}" target="_blank" class="mt-1 block text-xs text-brand-deeper hover:underline">Lihat bukti</a>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            @if ($subscription->status === 'waiting_verification')
                                <form method="POST" action="{{ route('admin.cv-subscriptions.verify', $subscription) }}" class="mb-3">
                                    @csrf
                                    <input type="hidden" name="status" value="active">
                                    <button class="w-full rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700" type="submit">
                                        ✓ Aktifkan paket sekarang
                                    </button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('admin.cv-subscriptions.verify', $subscription) }}" class="space-y-2">
                                @csrf
                                <select name="status" class="input-field py-1 text-xs">
                                    <option value="active" @selected(in_array($subscription->status, ['active', 'waiting_verification'], true))>Aktifkan paket</option>
                                    <option value="waiting_verification">Menunggu verifikasi</option>
                                    <option value="rejected" @selected($subscription->status === 'rejected')>Tolak</option>
                                    <option value="expired" @selected($subscription->status === 'expired')>Expired</option>
                                </select>
                                <input type="text" name="admin_note" value="{{ $subscription->admin_note }}" class="input-field py-1 text-xs" placeholder="Catatan admin">
                                <button class="btn-primary w-full text-xs" type="submit">Simpan status</button>
                            </form>
                            @if ($subscription->status === 'active')
                                <p class="mt-2 text-[11px] font-medium text-emerald-700">Paket aktif · sisa {{ $subscription->remainingReviews() === null ? '∞' : $subscription->remainingReviews().'x' }}</p>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-ink-soft">Belum ada pembayaran paket Review CV AI.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $cvSubscriptions->links() }}</div>
</div>

<div>
    <div class="mb-3">
        <h2 class="font-display text-lg font-semibold text-ink">Pembayaran Program / Bootcamp</h2>
        <p class="text-sm text-ink-soft">Verifikasi pembayaran kelas seperti biasa.</p>
    </div>

    <div class="card-soft overflow-hidden">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-brand-mist/60 text-ink-soft">
                <tr>
                    <th class="px-5 py-3 font-medium">Invoice</th>
                    <th class="px-5 py-3 font-medium">User</th>
                    <th class="px-5 py-3 font-medium">Program</th>
                    <th class="px-5 py-3 font-medium">Nominal</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 font-medium">Verifikasi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payments as $payment)
                    <tr class="border-t border-brand/10 align-top">
                        <td class="px-5 py-3 font-medium">{{ $payment->invoice_code }}</td>
                        <td class="px-5 py-3">
                            <p>{{ $payment->user->name }}</p>
                            <p class="text-xs text-ink-soft">{{ $payment->user->email }}</p>
                        </td>
                        <td class="px-5 py-3">{{ $payment->program?->title ?? '—' }}</td>
                        <td class="px-5 py-3">{{ $payment->formattedAmount() }}</td>
                        <td class="px-5 py-3">
                            <span class="badge">{{ str_replace('_', ' ', $payment->status) }}</span>
                            @if ($payment->proof_path)
                                <a href="{{ media_url($payment->proof_path) }}" target="_blank" class="mt-1 block text-xs text-brand-deeper hover:underline">Lihat bukti</a>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <form method="POST" action="{{ route('admin.payments.verify', $payment) }}" class="space-y-2">
                                @csrf
                                <select name="status" class="input-field py-1 text-xs">
                                    <option value="paid" @selected($payment->status === 'paid' || $payment->status === 'waiting_verification')>Terima / Paid — buka akses kelas</option>
                                    <option value="waiting_verification">Menunggu verifikasi</option>
                                    <option value="rejected" @selected($payment->status === 'rejected')>Tolak</option>
                                    <option value="refunded" @selected($payment->status === 'refunded')>Refund</option>
                                </select>
                                <input type="text" name="admin_note" value="{{ $payment->admin_note }}" class="input-field py-1 text-xs" placeholder="Catatan admin">
                                <button class="btn-primary w-full text-xs" type="submit">Simpan verifikasi</button>
                            </form>
                            @if ($payment->status === 'paid' && $payment->enrollment)
                                <p class="mt-2 text-[11px] font-medium text-emerald-700">Akses kelas aktif</p>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-ink-soft">Belum ada pembayaran program.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $payments->links() }}</div>
</div>
@endsection
