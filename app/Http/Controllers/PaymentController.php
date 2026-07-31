<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\Payment;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(): View
    {
        $payments = Payment::with('program')->where('user_id', auth()->id())->latest()->get();

        return view('payments.index', compact('payments'));
    }

    public function checkout(Program $program): View|RedirectResponse
    {
        abort_unless($program->is_published, 404);
        if ($program->isFree()) {
            return redirect()->route('programs.enroll', $program);
        }

        $program->load(['mentor', 'partner']);

        return view('payments.checkout', compact('program'));
    }

    public function store(Request $request, Program $program): RedirectResponse
    {
        $request->validate(['proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120']]);

        $path = $request->file('proof')->store('payments', media_disk());

        $payment = Payment::create([
            'user_id' => auth()->id(),
            'program_id' => $program->id,
            'amount' => $program->price,
            'invoice_code' => 'INV-'.strtoupper(Str::random(8)),
            'proof_path' => $path,
            'status' => 'waiting_verification',
        ]);

        AppNotification::create([
            'user_id' => auth()->id(),
            'title' => 'Pembayaran dikirim',
            'body' => 'Menunggu verifikasi admin untuk invoice '.$payment->invoice_code,
            'type' => 'payment',
            'link' => route('payments.invoice', $payment),
        ]);

        return redirect()
            ->route('payments.invoice', $payment)
            ->with('success', 'Bukti pembayaran diunggah. Menunggu verifikasi admin.');
    }

    public function invoice(Payment $payment): View
    {
        abort_unless($payment->user_id === auth()->id() || auth()->user()->isAdmin(), 403);
        $payment->load(['program', 'user']);

        return view('payments.invoice', compact('payment'));
    }
}
