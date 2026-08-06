<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = AppNotification::where('user_id', auth()->id())
            ->latest()
            ->paginate(20);

        $highlightId = (int) request('highlight', 0);

        return view('notifications.index', compact('notifications', 'highlightId'));
    }

    public function open(AppNotification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === auth()->id(), 403);

        if (! $notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        Cache::forget('notif-bell-'.auth()->id());

        if (filled($notification->link)) {
            return redirect()->to($notification->link);
        }

        return redirect()
            ->route('notifications.index', ['highlight' => $notification->id])
            ->with('success', 'Notifikasi ditandai sudah dibaca.');
    }

    public function read(AppNotification $notification): RedirectResponse
    {
        return $this->open($notification);
    }

    public function readAll(): RedirectResponse
    {
        AppNotification::query()
            ->where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        Cache::forget('notif-bell-'.auth()->id());

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}
