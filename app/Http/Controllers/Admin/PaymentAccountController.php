<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentAccountController extends Controller
{
    public function edit(): View
    {
        return view('admin.payment-account.edit', [
            'account' => PaymentAccount::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'bank_name' => ['required', 'string', 'max:80'],
            'account_number' => ['required', 'string', 'max:64', 'regex:/^[0-9\-\s]+$/'],
            'account_holder' => ['required', 'string', 'max:160'],
        ], [
            'account_number.regex' => 'Nomor rekening hanya boleh angka, spasi, atau tanda hubung.',
        ]);

        $account = PaymentAccount::current();
        $account->update([
            'bank_name' => trim($data['bank_name']),
            'account_number' => preg_replace('/\s+/', '', $data['account_number']) ?? $data['account_number'],
            'account_holder' => trim($data['account_holder']),
            'is_active' => true,
        ]);

        return back()->with('success', 'Rekening pembayaran berhasil diperbarui. Perubahan langsung tampil di halaman checkout.');
    }
}
