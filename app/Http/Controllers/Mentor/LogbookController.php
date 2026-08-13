<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\LogbookEntry;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogbookController extends Controller
{
    public function index(): View
    {
        $programIds = Program::query()
            ->where('mentor_id', auth()->id())
            ->where('type', 'internship')
            ->pluck('id');

        $entries = LogbookEntry::query()
            ->with(['user', 'program'])
            ->whereIn('program_id', $programIds)
            ->latest('entry_date')
            ->paginate(20);

        return view('mentor.logbooks.index', compact('entries'));
    }

    public function review(Request $request, LogbookEntry $logbook): RedirectResponse
    {
        abort_unless($logbook->program?->mentor_id === auth()->id(), 403);

        $data = $request->validate([
            'status' => ['required', 'in:reviewed,revision,submitted'],
            'reviewer_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $logbook->update([
            'status' => $data['status'],
            'reviewer_note' => $data['reviewer_note'] ?? null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        notify_user(
            $logbook->user_id,
            'Logbook direview',
            'Mentor meninjau entri "'.$logbook->title.'".',
            $data['status'] === 'revision' ? 'warning' : 'info',
            route('profile.logbook')
        );

        return back()->with('success', 'Review logbook disimpan.');
    }
}
