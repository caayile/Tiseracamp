<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Category;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\Program;
use App\Models\QuizQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProgramController extends Controller
{
    public function index(): View
    {
        $programs = Program::where('mentor_id', auth()->id())
            ->where('type', 'bootcamp')
            ->with(['category', 'mentor', 'partner'])
            ->latest()
            ->get();

        return view('mentor.programs.index', compact('programs'));
    }

    public function create(): View
    {
        return view('mentor.programs.form', [
            'program' => new Program(['type' => 'bootcamp', 'level' => 'Beginner']),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'level' => ['required', 'string'],
            'duration_months' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'integer', 'min:0'],
            'excerpt' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'benefits_text' => ['nullable', 'string'],
        ]);

        Program::create([
            'title' => $data['title'],
            'slug' => Str::slug($data['title']).'-'.Str::random(4),
            'type' => 'bootcamp',
            'level' => $data['level'],
            'duration_months' => $data['duration_months'],
            'price' => $data['price'],
            'excerpt' => $data['excerpt'] ?? null,
            'description' => $data['description'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'thumbnail' => null,
            'benefits' => collect(preg_split('/\r\n|\r|\n/', $data['benefits_text'] ?? ''))
                ->map(fn ($l) => trim($l))->filter()->values()->all(),
            'qualifications' => [],
            'mentor_id' => auth()->id(),
            'is_published' => false,
            'approval_status' => 'pending',
        ]);

        return redirect()->route('mentor.programs.index')->with('success', 'Bootcamp diajukan. Menunggu approve admin.');
    }

    public function curriculum(Program $program): View
    {
        abort_unless($program->mentor_id === auth()->id(), 403);
        abort_unless($program->type === 'bootcamp', 404);
        $program->load(['modules.lessons.assignment.questions']);

        return view('mentor.programs.curriculum', compact('program'));
    }

    public function storeModule(Request $request, Program $program): RedirectResponse
    {
        abort_unless($program->mentor_id === auth()->id(), 403);
        $data = $request->validate(['title' => ['required', 'string', 'max:160']]);
        $program->modules()->create([
            'title' => $data['title'],
            'sort_order' => $program->modules()->count() + 1,
        ]);

        return back()->with('success', 'Modul ditambahkan.');
    }

    public function storeLesson(Request $request, Module $module): RedirectResponse
    {
        abort_unless($module->program->mentor_id === auth()->id(), 403);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'type' => ['required', 'in:video,text,quiz,pdf,article,recording'],
            'content' => ['nullable', 'string'],
            'video_url' => ['nullable', 'url'],
            'file_url' => ['nullable', 'url'],
            'pdf_file' => ['nullable', 'file', 'mimes:pdf', 'max:15360'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'instructions' => ['nullable', 'string'],
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
            ? $this->sanitizeRichText($data['content'] ?? null)
            : null;

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

            return back()->with('success', "Quiz ditambahkan ({$saved} soal).");
        }

        return back()->with('success', 'Materi ditambahkan.');
    }

    private function sanitizeRichText(?string $html): ?string
    {
        if (! filled($html)) {
            return null;
        }

        $html = strip_tags($html, '<div><p><br><strong><b><em><i><u><span><font>');
        $document = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="UTF-8"><div id="rich-root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('rich-root');
        if (! $root) {
            return null;
        }

        foreach (iterator_to_array($document->getElementsByTagName('*')) as $element) {
            foreach (iterator_to_array($element->attributes ?? []) as $attribute) {
                $keepFontSize = $element->tagName === 'font'
                    && $attribute->name === 'size'
                    && preg_match('/^[1-6]$/', $attribute->value);

                if ($element !== $root && ! $keepFontSize) {
                    $element->removeAttribute($attribute->name);
                }
            }
        }

        $clean = '';
        foreach ($root->childNodes as $child) {
            $clean .= $document->saveHTML($child);
        }

        return trim($clean) ?: null;
    }

    public function students(Program $program): View
    {
        abort_unless($program->mentor_id === auth()->id(), 403);
        abort_unless($program->type === 'bootcamp', 404);
        $enrollments = Enrollment::with(['user', 'certificate'])
            ->where('program_id', $program->id)
            ->latest()
            ->get();

        return view('mentor.programs.students', compact('program', 'enrollments'));
    }

    public function rateStudent(Request $request, Enrollment $enrollment): RedirectResponse
    {
        $enrollment->load('program');
        abort_unless($enrollment->program->mentor_id === auth()->id(), 403);
        abort_unless($enrollment->program->type === 'bootcamp', 404);
        abort_unless($enrollment->isCompleted(), 403, 'Rating hanya untuk siswa yang sudah menyelesaikan semua materi.');

        $data = $request->validate([
            'mentor_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'mentor_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $enrollment->update([
            'mentor_rating' => $data['mentor_rating'],
            'mentor_note' => $data['mentor_note'] ?? null,
            'mentor_rated_at' => now(),
        ]);

        AppNotification::create([
            'user_id' => $enrollment->user_id,
            'title' => 'Rating dari mentor',
            'body' => 'Mentor memberi '.$data['mentor_rating'].'★ untuk program '.$enrollment->program->title,
            'type' => 'info',
            'link' => route('learn.show', $enrollment->program),
        ]);

        return back()->with('success', 'Rating siswa disimpan.');
    }
}
