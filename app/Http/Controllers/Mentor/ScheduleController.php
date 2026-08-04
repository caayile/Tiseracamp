<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\ClassSchedule;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(): View
    {
        $schedules = ClassSchedule::with('program')->where('mentor_id', auth()->id())->orderByDesc('starts_at')->get();
        $programs = Program::where('mentor_id', auth()->id())->where('type', 'bootcamp')->get();

        return view('mentor.schedules.index', compact('schedules', 'programs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'program_id' => ['required', 'exists:programs,id'],
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'meeting_url' => ['nullable', 'url'],
            'materials_url' => ['nullable', 'url'],
            'materials_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $program = Program::findOrFail($data['program_id']);
        abort_unless($program->mentor_id === auth()->id(), 403);

        ClassSchedule::create([
            ...$data,
            'mentor_id' => auth()->id(),
            'status' => 'scheduled',
        ]);

        foreach ($program->enrollments as $enrollment) {
            AppNotification::create([
                'user_id' => $enrollment->user_id,
                'title' => 'Jadwal kelas baru',
                'body' => $data['title'].' — '.$program->title,
                'type' => 'schedule',
                'link' => route('schedules.index'),
            ]);
        }

        return back()->with('success', 'Jadwal dibuat.');
    }

    public function uploadRecording(Request $request, ClassSchedule $schedule): RedirectResponse
    {
        abort_unless($schedule->mentor_id === auth()->id(), 403);
        $data = $request->validate(['recording_url' => ['required', 'url']]);
        $schedule->update(['recording_url' => $data['recording_url'], 'status' => 'done']);

        return back()->with('success', 'Recording diunggah.');
    }
}
