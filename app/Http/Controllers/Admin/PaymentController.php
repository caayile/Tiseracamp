<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\Enrollment;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(): View
    {
        $payments = Payment::with(['user', 'program'])->latest()->paginate(20);

        return view('admin.payments.index', compact('payments'));
    }

    public function verify(Request $request, Payment $payment): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:paid,rejected,refunded'],
            'admin_note' => ['nullable', 'string'],
        ]);

        $payment->update([
            'status' => $data['status'],
            'admin_note' => $data['admin_note'] ?? null,
            'paid_at' => $data['status'] === 'paid' ? now() : null,
        ]);

        if ($data['status'] === 'paid') {
            $enrollment = Enrollment::firstOrCreate(
                ['user_id' => $payment->user_id, 'program_id' => $payment->program_id],
                ['status' => 'active', 'progress' => 0, 'enrolled_at' => now()]
            );
            $payment->update(['enrollment_id' => $enrollment->id]);
        }

        AppNotification::create([
            'user_id' => $payment->user_id,
            'title' => 'Update pembayaran',
            'body' => 'Status invoice '.$payment->invoice_code.': '.$data['status'],
            'type' => 'payment',
            'link' => route('payments.index'),
        ]);

        ActivityLog::record(auth()->user(), 'verify_payment', $payment, $data['status']);

        return back()->with('success', 'Status pembayaran diperbarui.');
    }
}
