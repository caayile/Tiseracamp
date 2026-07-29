@extends('layouts.admin')

@section('title', 'Pembayaran')
@section('heading', 'Verifikasi Pembayaran')

@section('content')
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
                    <td class="px-5 py-3">{{ $payment->program->title }}</td>
                    <td class="px-5 py-3">{{ $payment->formattedAmount() }}</td>
                    <td class="px-5 py-3">
                        <span class="badge">{{ str_replace('_', ' ', $payment->status) }}</span>
                        @if ($payment->proof_path)
                            <a href="{{ asset('storage/'.$payment->proof_path) }}" target="_blank" class="mt-1 block text-xs text-brand-deeper hover:underline">Lihat bukti</a>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        <form method="POST" action="{{ route('admin.payments.verify', $payment) }}" class="space-y-2">
                            @csrf
                            <select name="status" class="input-field py-1 text-xs">
                                <option value="paid" @selected($payment->status === 'paid')>Paid</option>
                                <option value="waiting_verification" @selected($payment->status === 'waiting_verification')>Waiting</option>
                                <option value="rejected" @selected($payment->status === 'rejected')>Rejected</option>
                                <option value="refunded" @selected($payment->status === 'refunded')>Refunded</option>
                            </select>
                            <input type="text" name="admin_note" value="{{ $payment->admin_note }}" class="input-field py-1 text-xs" placeholder="Catatan admin">
                            <button class="btn-primary w-full text-xs" type="submit">Verifikasi</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-5 py-8 text-center text-ink-soft">Belum ada pembayaran.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">{{ $payments->links() }}</div>
@endsection
