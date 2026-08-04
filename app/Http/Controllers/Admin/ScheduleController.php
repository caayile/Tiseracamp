<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\ClassSchedule;
use App\Models\Conversation;
use App\Models\Enrollment;
use App\Models\Message;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(): View
    {
        $schedules = ClassSchedule::with(['program', 'mentor'])
            ->whereHas('program', fn ($q) => $q->where('type', 'internship'))
            ->orderByDesc('starts_at')
            ->get();

        $programs = Program::query()
            ->where('type', 'internship')
            ->withCount('enrollments')
            ->orderBy('title')
            ->get();

        $mentors = User::query()
            ->where('role', 'mentor')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('admin.schedules.index', compact('schedules', 'programs', 'mentors'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'program_id' => ['required', 'exists:programs,id'],
            'mentor_id' => ['nullable', 'exists:users,id'],
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'meeting_url' => ['required', 'url', 'max:500'],
            'materials_url' => ['nullable', 'url', 'max:500'],
            'materials_note' => ['nullable', 'string', 'max:2000'],
            'notify_students' => ['nullable', 'boolean'],
            'notify_chat' => ['nullable', 'boolean'],
        ]);

        $program = Program::findOrFail($data['program_id']);
        abort_unless($program->type === 'internship', 422, 'Sesi ini khusus program magang.');

        $staffId = $data['mentor_id']
            ?? $program->mentor_id
            ?? auth()->id();

        $schedule = ClassSchedule::create([
            'program_id' => $program->id,
            'mentor_id' => $staffId,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'] ?? null,
            'meeting_url' => $data['meeting_url'],
            'materials_url' => $data['materials_url'] ?? null,
            'materials_note' => $data['materials_note'] ?? null,
            'status' => 'scheduled',
        ]);

        $students = $this->internshipStudents($program);
        $notified = 0;

        if ($request->boolean('notify_students')) {
            $notified = $this->notifyStudents($students, $schedule, $program);
        }

        $chatted = 0;
        if ($request->boolean('notify_chat')) {
            $chatted = $this->blastChat($students, $schedule, $program, $staffId);
        }

        return back()->with(
            'success',
            "Sesi magang dibuat. Notifikasi: {$notified} siswa · Chat: {$chatted} siswa."
        );
    }

    public function update(Request $request, ClassSchedule $schedule): RedirectResponse
    {
        abort_unless($schedule->program?->type === 'internship', 404);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'meeting_url' => ['required', 'url', 'max:500'],
            'materials_url' => ['nullable', 'url', 'max:500'],
            'materials_note' => ['nullable', 'string', 'max:2000'],
            'mentor_id' => ['nullable', 'exists:users,id'],
            'status' => ['required', 'in:scheduled,live,done'],
            'renotify' => ['nullable', 'boolean'],
        ]);

        $schedule->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'] ?? null,
            'meeting_url' => $data['meeting_url'],
            'materials_url' => $data['materials_url'] ?? null,
            'materials_note' => $data['materials_note'] ?? null,
            'mentor_id' => $data['mentor_id'] ?: $schedule->mentor_id,
            'status' => $data['status'],
        ]);

        if ($request->boolean('renotify')) {
            $program = $schedule->program;
            $students = $this->internshipStudents($program);
            $this->notifyStudents($students, $schedule->fresh(), $program, updated: true);
            $this->blastChat(
                $students,
                $schedule->fresh(),
                $program,
                $schedule->mentor_id ?? auth()->id(),
                updated: true
            );
        }

        return back()->with('success', 'Sesi magang diperbarui.');
    }

    public function destroy(ClassSchedule $schedule): RedirectResponse
    {
        abort_unless($schedule->program?->type === 'internship', 404);
        $schedule->delete();

        return back()->with('success', 'Sesi magang dihapus.');
    }

    /** @return Collection<int, User> */
    private function internshipStudents(Program $program): Collection
    {
        return Enrollment::query()
            ->with('user')
            ->where('program_id', $program->id)
            ->whereIn('status', ['active', 'completed'])
            ->get()
            ->pluck('user')
            ->filter();
    }

    private function scheduleMessage(ClassSchedule $schedule, Program $program, bool $updated = false): string
    {
        $when = $schedule->starts_at->timezone(config('app.timezone'))->translatedFormat('l, d M Y · H:i');
        $lines = [
            ($updated ? 'Update sesi magang: ' : 'Sesi magang baru: ').$schedule->title,
            'Program: '.$program->title,
            'Jadwal: '.$when,
            'Link Meet: '.$schedule->meeting_url,
        ];

        if ($schedule->materials_url) {
            $lines[] = 'Materi: '.$schedule->materials_url;
        }
        if ($schedule->materials_note) {
            $lines[] = 'Arahan: '.$schedule->materials_note;
        }
        if ($schedule->description) {
            $lines[] = 'Agenda: '.$schedule->description;
        }

        return implode("\n", $lines);
    }

    private function notifyStudents(Collection $students, ClassSchedule $schedule, Program $program, bool $updated = false): int
    {
        $count = 0;
        $when = $schedule->starts_at->translatedFormat('d M Y, H:i');

        foreach ($students as $student) {
            AppNotification::create([
                'user_id' => $student->id,
                'title' => $updated ? 'Update sesi magang' : 'Sesi magang terjadwal',
                'body' => $schedule->title.' · '.$when.' · '.$program->title,
                'type' => 'schedule',
                'link' => route('schedules.index'),
            ]);
            $count++;
        }

        return $count;
    }

    private function blastChat(
        Collection $students,
        ClassSchedule $schedule,
        Program $program,
        int $staffId,
        bool $updated = false
    ): int {
        $body = $this->scheduleMessage($schedule, $program, $updated);
        $count = 0;

        foreach ($students as $student) {
            $conversation = Conversation::firstOrCreate([
                'program_id' => $program->id,
                'student_id' => $student->id,
                'mentor_id' => $staffId,
            ]);

            Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => auth()->id(),
                'body' => $body,
            ]);

            AppNotification::create([
                'user_id' => $student->id,
                'title' => $updated ? 'Chat: update sesi magang' : 'Chat: sesi magang baru',
                'body' => $schedule->title.' — buka chat untuk link Meet',
                'type' => 'chat',
                'link' => route('chat.show', $conversation),
            ]);

            $count++;
        }

        return $count;
    }
}
