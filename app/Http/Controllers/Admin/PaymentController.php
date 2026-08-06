<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\CvSubscription;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(): View
    {
        $payments = Payment::with(['user', 'program', 'enrollment'])->latest()->paginate(20);
        $cvSubscriptions = CvSubscription::with('user')
            ->latest()
            ->paginate(20, ['*'], 'cv_page');

        return view('admin.payments.index', compact('payments', 'cvSubscriptions'));
    }

    public function verify(Request $request, Payment $payment): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:paid,waiting_verification,rejected,refunded'],
            'admin_note' => ['nullable', 'string'],
        ]);

        $payment->update([
            'status' => $data['status'],
            'admin_note' => $data['admin_note'] ?? null,
            'paid_at' => $data['status'] === 'paid' ? ($payment->paid_at ?? now()) : null,
        ]);

        if ($data['status'] === 'paid') {
            $enrollment = $payment->grantClassAccess();

            AppNotification::create([
                'user_id' => $payment->user_id,
                'title' => 'Pembayaran diterima',
                'body' => 'Invoice '.$payment->invoice_code.' lunas. Kamu sudah bisa masuk kelas '.$payment->program->title.'.',
                'type' => 'payment',
                'link' => route('learn.show', $payment->program),
            ]);

            ActivityLog::record(auth()->user(), 'verify_payment', $payment, 'paid · enrollment #'.$enrollment->id);

            return back()->with('success', 'Pembayaran diterima. Akses kelas siswa sudah dibuka.');
        }

        AppNotification::create([
            'user_id' => $payment->user_id,
            'title' => 'Update pembayaran',
            'body' => 'Status invoice '.$payment->invoice_code.': '.str_replace('_', ' ', $data['status']),
            'type' => 'payment',
            'link' => route('payments.invoice', $payment),
        ]);

        ActivityLog::record(auth()->user(), 'verify_payment', $payment, $data['status']);

        return back()->with('success', 'Status pembayaran diperbarui.');
    }
}
