<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\LogbookEntry;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LogbookController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'program_id' => ['required', 'exists:programs,id'],
            'entry_date' => ['required', 'date'],
            'title' => ['required', 'string', 'max:160'],
            'body' => ['required', 'string', 'max:5000'],
            'obstacles' => ['nullable', 'string', 'max:5000'],
            'hours' => ['required', 'integer', 'min:1', 'max:24'],
            'attachment' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $enrollment = Enrollment::where('user_id', auth()->id())
            ->where('program_id', $data['program_id'])
            ->firstOrFail();

        $program = Program::findOrFail($data['program_id']);
        abort_unless($program->type === 'internship', 403);

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('logbooks', media_disk());
        }

        LogbookEntry::create([
            'user_id' => auth()->id(),
            'program_id' => $program->id,
            'enrollment_id' => $enrollment->id,
            'entry_date' => $data['entry_date'],
            'title' => $data['title'],
            'body' => $data['body'],
            'obstacles' => $data['obstacles'] ?? null,
            'hours' => $data['hours'],
            'attachment_path' => $path,
        ]);

        return redirect()
            ->route('profile.logbook')
            ->with('success', 'Entri logbook disimpan.');
    }

    public function destroy(LogbookEntry $logbook): RedirectResponse
    {
        abort_unless($logbook->user_id === auth()->id(), 403);
        $logbook->delete();

        return redirect()
            ->route('profile.logbook')
            ->with('success', 'Entri logbook dihapus.');
    }
}
