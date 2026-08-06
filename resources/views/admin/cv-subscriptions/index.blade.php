@extends('layouts.admin')

@section('title', 'Paket CV AI')
@section('heading', 'Verifikasi Paket Review CV AI')

@section('content')
<p class="mb-4 text-sm text-ink-soft">Untuk invoice menunggu verifikasi: klik tombol hijau <strong>Aktifkan paket sekarang</strong>.</p>

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
            @forelse ($subscriptions as $subscription)
                <tr class="border-t border-brand/10 align-top {{ $subscription->status === 'waiting_verification' ? 'bg-amber-50/40' : '' }}">
                    <td class="px-5 py-3 font-medium">{{ $subscription->invoice_code }}</td>
                    <td class="px-5 py-3">
                        <p>{{ $subscription->user->name }}</p>
                        <p class="text-xs text-ink-soft">{{ $subscription->user->email }}</p>
                    </td>
                    <td class="px-5 py-3">
                        <p>{{ $subscription->plan_name }}</p>
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
                            <input type="text" name="admin_note" value="{{ old('admin_note', $subscription->admin_note) }}" class="input-field py-1 text-xs" placeholder="Catatan admin (opsional)">
                            <button class="btn-primary w-full text-xs" type="submit">Simpan status</button>
                        </form>

                        @if ($subscription->status === 'active')
                            <p class="mt-2 text-[11px] font-medium text-emerald-700">
                                Aktif · sisa {{ $subscription->remainingReviews() === null ? '∞' : $subscription->remainingReviews().'x' }}
                                @if ($subscription->ends_at)
                                    · sampai {{ $subscription->ends_at->format('d M Y') }}
                                @endif
                            </p>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-5 py-8 text-center text-ink-soft">Belum ada pembayaran paket CV.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6 flex justify-center">
    {{ $subscriptions->links() }}
</div>
@endsection
