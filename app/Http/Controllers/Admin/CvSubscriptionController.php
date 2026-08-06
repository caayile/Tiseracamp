<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\CvSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CvSubscriptionController extends Controller
{
    public function index(): View
    {
        $subscriptions = CvSubscription::with('user')->latest()->paginate(20);

        return view('admin.cv-subscriptions.index', compact('subscriptions'));
    }

    public function verify(Request $request, CvSubscription $subscription): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:active,waiting_verification,rejected,expired'],
            'admin_note' => ['nullable', 'string'],
        ]);

        if ($data['status'] === 'active') {
            CvSubscription::query()
                ->where('user_id', $subscription->user_id)
                ->where('id', '!=', $subscription->id)
                ->where('status', 'active')
                ->update(['status' => 'expired']);

            $subscription->activate();
            $subscription->update(['admin_note' => $data['admin_note'] ?? $subscription->admin_note]);

            AppNotification::create([
                'user_id' => $subscription->user_id,
                'title' => 'Paket Review CV AI aktif',
                'body' => 'Invoice '.$subscription->invoice_code.' diverifikasi. Silakan mulai Review CV AI.',
                'type' => 'payment',
                'link' => route('cv-review.index'),
            ]);

            ActivityLog::record(auth()->user(), 'verify_cv_subscription', $subscription, 'active');

            return back()->with('success', 'Paket CV diaktifkan. Siswa bisa mulai review.');
        }

        $subscription->update([
            'status' => $data['status'],
            'admin_note' => $data['admin_note'] ?? null,
            'paid_at' => null,
        ]);

        AppNotification::create([
            'user_id' => $subscription->user_id,
            'title' => 'Update paket Review CV AI',
            'body' => 'Status invoice '.$subscription->invoice_code.': '.str_replace('_', ' ', $data['status']),
            'type' => 'payment',
            'link' => route('cv-review.plans'),
        ]);

        ActivityLog::record(auth()->user(), 'verify_cv_subscription', $subscription, $data['status']);

        return back()->with('success', 'Status paket CV diperbarui.');
    }
}
