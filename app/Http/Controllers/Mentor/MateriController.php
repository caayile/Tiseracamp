<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Mentor\ProgramController as BaseProgramController;
use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MateriController extends Controller
{
    public function index(Module $module): View
    {
        abort_unless($module->program->mentor_id === auth()->id(), 403);

        $lessons = $module->lessons()->latest()->get();

        return view('mentor.programs.materials.index', compact('module', 'lessons'));
    }

    public function create(Module $module): View
    {
        abort_unless($module->program->mentor_id === auth()->id(), 403);

        return view('mentor.programs.materials.create', compact('module'));
    }

    public function store(Request $request, Module $module): RedirectResponse
    {
        abort_unless($module->program->mentor_id === auth()->id(), 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:text,video,article,pdf,recording,quiz,assignment'],
            'content' => ['nullable', 'string'],
            'video_url' => ['nullable', 'url'],
            'file_url' => ['nullable', 'url'],
            'pdf_file' => ['nullable', 'file', 'mimes:pdf', 'max:15360'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'instructions' => ['nullable', 'string'],
            'deadline' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:5000'],
            'questions' => ['nullable', 'array', 'max:50'],
            'questions.*.question' => ['nullable', 'string', 'max:500'],
            'questions.*.options' => ['nullable', 'array', 'max:4'],
            'questions.*.options.*' => ['nullable', 'string', 'max:255'],
            'questions.*.correct_index' => ['nullable', 'integer', 'min:0', 'max:3'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if ($data['type'] === 'pdf' && ! $request->hasFile('pdf_file') && blank($data['file_url'] ?? null)) {
            return back()
                ->withInput()
                ->withErrors(['pdf_file' => 'Upload file PDF atau tempel link PDF.']);
        }

        $content = in_array($data['type'], ['text', 'article'], true)
            ? null
            : ($data['content'] ?? null);

        if ($data['type'] === 'pdf') {
            $description = trim((string) ($data['description'] ?? ''));
            $content = $description !== '' ? nl2br(e($description)) : null;
        }

        $imagePath = null;
        if ($request->hasFile('image') && in_array($data['type'], ['text', 'article'], true)) {
            $imagePath = $request->file('image')->store('lesson-images', media_disk());
        }

        $fileUrl = $data['file_url'] ?? null;
        $fileType = null;
        if ($data['type'] === 'pdf' && $request->hasFile('pdf_file')) {
            $fileUrl = $request->file('pdf_file')->store('lesson-pdfs', media_disk());
            $fileType = 'pdf';
        } elseif (filled($fileUrl) && str_contains(strtolower($fileUrl), '.pdf')) {
            $fileType = 'pdf';
        }

        $lesson = $module->lessons()->create([
            'title' => $data['title'],
            'type' => $data['type'],
            'content' => $content,
            'video_url' => $data['video_url'] ?? null,
            'file_url' => $fileUrl,
            'file_type' => $fileType,
            'image_path' => $imagePath,
            'duration_minutes' => $data['duration_minutes'] ?? ($data['type'] === 'text' ? 10 : 15),
            'sort_order' => $module->lessons()->count() + 1,
        ]);

        if ($data['type'] === 'assignment') {
            $lesson->assignment()->create([
                'title' => $data['title'],
                'instructions' => $data['instructions'] ?? null,
                'deadline' => $data['deadline'] ?? null,
                'kind' => 'assignment',
            ]);

            return back()->with('success', 'Tugas ditambahkan. Peserta magang langsung melihatnya dan mengumpulkan lewat tautan.');
        }

        if ($data['type'] === 'quiz') {
            $assignment = $lesson->assignment()->create([
                'title' => $data['title'],
                'instructions' => $data['instructions'] ?? null,
                'kind' => 'quiz',
            ]);

            $saved = 0;
            foreach ($data['questions'] ?? [] as $row) {
                $question = trim((string) ($row['question'] ?? ''));
                if ($question === '') {
                    continue;
                }

                $options = collect($row['options'] ?? [])
                    ->map(fn ($opt) => trim((string) $opt))
                    ->filter()
                    ->values()
                    ->all();

                if (count($options) < 2) {
                    continue;
                }

                $correct = (int) ($row['correct_index'] ?? 0);
                if ($correct < 0 || $correct >= count($options)) {
                    $correct = 0;
                }

                QuizQuestion::create([
                    'assignment_id' => $assignment->id,
                    'question' => $question,
                    'options' => $options,
                    'correct_index' => $correct,
                    'points' => 10,
                ]);
                $saved++;
            }

            if ($saved === 0) {
                $assignment->delete();
                $lesson->delete();

                return back()
                    ->withInput()
                    ->withErrors(['questions' => 'Quiz minimal 1 soal dengan pertanyaan dan 2 opsi.']);
            }

            return back()->with('success', "Quiz ditambahkan ({$saved} soal). Peserta langsung melihatnya di ruang belajar.");
        }

        return back()->with('success', 'Materi ditambahkan. Peserta magang langsung melihatnya di ruang belajar.');
    }

    public function edit(Lesson $lesson): View
    {
        abort_unless($lesson->module->program->mentor_id === auth()->id(), 403);

        $program = $lesson->module->program;

        $typeLabels = [
            'text' => 'Pengenalan',
            'video' => 'Video',
            'article' => 'Artikel',
            'pdf' => 'PDF',
            'recording' => 'Rekaman',
            'quiz' => 'Quiz',
            'assignment' => 'Tugas',
        ];

        return view('mentor.programs.materials.edit', compact('lesson', 'typeLabels', 'program'));
    }

    public function update(Request $request, Lesson $lesson): RedirectResponse
    {
        abort_unless($lesson->module->program->mentor_id === auth()->id(), 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:text,video,article,pdf,recording,quiz,assignment'],
            'content' => ['nullable', 'string'],
            'video_url' => ['nullable', 'url'],
            'file_url' => ['nullable', 'url'],
            'pdf_file' => ['nullable', 'file', 'mimes:pdf', 'max:15360'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'instructions' => ['nullable', 'string'],
            'deadline' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:5000'],
            'questions' => ['nullable', 'array', 'max:50'],
            'questions.*.question' => ['nullable', 'string', 'max:500'],
            'questions.*.options' => ['nullable', 'array', 'max:4'],
            'questions.*.options.*' => ['nullable', 'string', 'max:255'],
            'questions.*.correct_index' => ['nullable', 'integer', 'min:0', 'max:3'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if ($data['type'] === 'pdf' && ! $request->hasFile('pdf_file') && blank($data['file_url'] ?? null)) {
            return back()
                ->withInput()
                ->withErrors(['pdf_file' => 'Upload file PDF atau tempel link PDF.']);
        }

        $content = in_array($data['type'], ['text', 'article'], true)
            ? null
            : ($data['content'] ?? null);

        if ($data['type'] === 'pdf') {
            $description = trim((string) ($data['description'] ?? ''));
            $content = $description !== '' ? nl2br(e($description)) : null;
        }

        $imagePath = $lesson->image_path;
        if ($request->hasFile('image') && in_array($lesson->type ?? 'text', ['text', 'article'], true)) {
            $imagePath = $request->file('image')->store('lesson-images', media_disk());
        }

        $fileUrl = $data['file_url'] ?? $lesson->file_url;
        $fileType = $lesson->file_type;
        if ($data['type'] === 'pdf' && $request->hasFile('pdf_file')) {
            $fileUrl = $request->file('pdf_file')->store('lesson-pdfs', media_disk());
            $fileType = 'pdf';
        } elseif (filled($fileUrl) && str_contains(strtolower($fileUrl), '.pdf')) {
            $fileType = 'pdf';
        }

        $lesson->update([
            'title' => $data['title'],
            'type' => $data['type'],
            'content' => $content,
            'video_url' => $data['video_url'] ?? null,
            'file_url' => $fileUrl,
            'file_type' => $fileType,
            'image_path' => $imagePath,
            'duration_minutes' => $data['duration_minutes'] ?? ($data['type'] === 'text' ? 10 : 15),
        ]);

        if ($data['type'] === 'assignment') {
            $lesson->assignment()->updateOrCreate(
                ['lesson_id' => $lesson->id],
                ['title' => $data['title'], 'instructions' => $data['instructions'] ?? null, 'deadline' => $data['deadline'] ?? null, 'kind' => 'assignment']
            );

            return back()->with('success', 'Tugas diperbarui. Peserta magang langsung melihatnya dan mengumpulkan lewat tautan.');
        }

        if ($data['type'] === 'quiz') {
            $assignment = $lesson->assignment()->updateOrCreate(
                ['lesson_id' => $lesson->id],
                ['title' => $data['title'], 'instructions' => $data['instructions'] ?? null, 'kind' => 'quiz']
            );

            if ($data['questions']) {
                $assignment->questions()->delete();

                $saved = 0;
                foreach ($data['questions'] ?? [] as $row) {
                    $question = trim((string) ($row['question'] ?? ''));
                    if ($question === '') {
                        continue;
                    }

                    $options = collect($row['options'] ?? [])
                        ->map(fn ($opt) => trim((string) $opt))
                        ->filter()
                        ->values()
                        ->all();

                    if (count($options) < 2) {
                        continue;
                    }

                    $correct = (int) ($row['correct_index'] ?? 0);
                    if ($correct < 0 || $correct >= count($options)) {
                        $correct = 0;
                    }

                    QuizQuestion::create([
                        'assignment_id' => $assignment->id,
                        'question' => $question,
                        'options' => $options,
                        'correct_index' => $correct,
                        'points' => 10,
                    ]);
                    $saved++;
                }
            }

            if ($saved > 0 || $assignment->questions->isNotEmpty()) {
                return back()->with('success', "Quiz diperbarui ({$saved} soal baru ditambahkan).");
            }
        }

        return back()->with('success', 'Materi diperbarui.');
    }

    public function destroy(Lesson $lesson): RedirectResponse
    {
        abort_unless($lesson->module->program->mentor_id === auth()->id(), 403);

        $lesson->load('assignment');
        $lesson->assignment?->questions()->delete();
        $lesson->assignment()?->delete();
        $lesson->delete();

        return back()->with('success', 'Materi dihapus.');
    }
}