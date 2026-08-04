<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = AppNotification::where('user_id', auth()->id())->latest()->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function read(AppNotification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === auth()->id(), 403);
        $notification->update(['read_at' => now()]);
        \Illuminate\Support\Facades\Cache::forget('notif-bell-'.auth()->id());

        return $notification->link ? redirect($notification->link) : back();
    }
}
