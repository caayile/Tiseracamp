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

    public function internships(): View
    {
        $programs = Program::where('mentor_id', auth()->id())
            ->where('type', 'internship')
            ->with(['category', 'mentor', 'partner', 'batches'])
            ->withCount('enrollments')
            ->latest()
            ->get();

        $available = Program::query()
            ->where('type', 'internship')
            ->whereNull('mentor_id')
            ->with(['batches'])
            ->withCount('enrollments')
            ->latest()
            ->get();

        return view('mentor.internships.index', compact('programs', 'available'));
    }

    public function createInternship(): View
    {
        return view('mentor.internships.form', [
            'program' => new Program([
                'type' => 'internship',
                'level' => 'Beginner',
                'duration_months' => 3,
            ]),
        ]);
    }

    public function claimInternship(Program $program): RedirectResponse
    {
        abort_unless($program->type === 'internship', 404);

        if ($program->mentor_id && $program->mentor_id !== auth()->id()) {
            return redirect()
                ->route('mentor.internships.index')
                ->with('error', 'Magang ini sudah diambil mentor lain.');
        }

        $program->update(['mentor_id' => auth()->id()]);
        $program->ensureInternshipWeeks();

        return redirect()
            ->route('mentor.internships.curriculum', $program)
            ->with('success', 'Magang diambil. Langsung isi tugas Minggu 1–4 — peserta melihatnya di ruang belajar.');
    }

    public function storeInternship(Request $request): RedirectResponse
    {
        $data = $this->validatedInternship($request);
        $quota = (int) $data['quota'];

        $program = Program::create([
            'title' => $data['title'],
            'slug' => Str::slug($data['title']).'-'.Str::random(4),
            'type' => 'internship',
            'level' => 'Beginner',
            'education_level' => $data['education_level'],
            'majors' => $data['majors'] ?? null,
            'division' => $data['division'] ?? null,
            'location' => $data['location'] ?? null,
            'deadline' => $data['deadline'] ?? null,
            'duration_months' => $data['duration_months'],
            'price' => 0,
            'description' => $data['description'] ?? null,
            'benefits' => $this->parseLines($request->input('benefits_text')),
            'qualifications' => $this->parseLines($request->input('qualifications_text')),
            'required_documents' => $this->parseLines($request->input('required_documents_text')),
            'preferred_skills' => $this->parseLines($request->input('preferred_skills_text')),
            'responsibilities' => $this->parseLines($request->input('responsibilities_text')),
            'mentor_id' => auth()->id(),
            'is_published' => true,
            'is_open' => true,
            'approval_status' => 'approved',
            'audience' => 'all',
        ]);

        $program->ensureInternshipWeeks();
        $program->syncInternshipQuota($quota);

        notify_admins(
            'Magang baru dari mentor',
            auth()->user()->name.' membuka '.$program->title.'.',
            'info',
            route('admin.programs.index', ['type' => 'internship'])
        );

        return redirect()
            ->route('mentor.internships.curriculum', $program)
            ->with('success', 'Magang dibuat. Isi materi Minggu 1–4 dan atur kuota peserta bila perlu.');
    }

    public function editInternship(Program $program): View
    {
        abort_unless($program->type === 'internship', 404);
        abort_unless($program->mentor_id === auth()->id(), 403);
        $program->load('batches');

        return view('mentor.internships.form', compact('program'));
    }

    public function updateInternship(Request $request, Program $program): RedirectResponse
    {
        abort_unless($program->type === 'internship', 404);
        abort_unless($program->mentor_id === auth()->id(), 403);

        $data = $this->validatedInternship($request, $program);
        $quota = (int) $data['quota'];

        $program->update([
            'title' => $data['title'],
            'education_level' => $data['education_level'],
            'majors' => $data['majors'] ?? null,
            'division' => $data['division'] ?? null,
            'location' => $data['location'] ?? null,
            'deadline' => $data['deadline'] ?? null,
            'duration_months' => $data['duration_months'],
            'description' => $data['description'] ?? null,
            'qualifications' => $this->parseLines($request->input('qualifications_text')),
            'required_documents' => $this->parseLines($request->input('required_documents_text')),
            'preferred_skills' => $this->parseLines($request->input('preferred_skills_text')),
            'benefits' => $this->parseLines($request->input('benefits_text')),
            'responsibilities' => $this->parseLines($request->input('responsibilities_text')),
            'price' => 0,
            'level' => 'Beginner',
            'audience' => 'all',
        ]);

        $program->syncInternshipQuota($quota);

        return redirect()
            ->route('mentor.internships.index')
            ->with('success', 'Detail magang dan kuota peserta diperbarui.');
    }

    public function updateInternshipQuota(Request $request, Program $program): RedirectResponse
    {
        abort_unless($program->type === 'internship', 404);
        abort_unless($program->mentor_id === auth()->id(), 403);

        $minQuota = max(1, $program->acceptedInternCount());
        $data = $request->validate([
            'quota' => ['required', 'integer', 'min:'.$minQuota, 'max:500'],
        ], [
            'quota.min' => 'Kuota tidak boleh lebih kecil dari jumlah peserta yang sudah diterima ('.$minQuota.').',
        ]);

        $program->syncInternshipQuota((int) $data['quota']);

        return back()->with('success', 'Kuota peserta magang diperbarui.');
    }

    public function internshipCurriculum(Program $program): View
    {
        abort_unless($program->type === 'internship', 404);
        abort_unless($program->mentor_id === auth()->id(), 403);
        $program->ensureInternshipWeeks();
        $program->load(['modules.lessons.assignment.questions', 'modules.lessons.assignment.submissions', 'batches']);
        $program->loadCount('enrollments');

        $audience = Enrollment::query()
            ->where('program_id', $program->id)
            ->whereIn('status', ['active', 'completed'])
            ->with('user:id,name,email')
            ->get()
            ->pluck('user')
            ->filter()
            ->unique('id')
            ->values();

        return view('mentor.internships.curriculum', compact('program', 'audience'));
    }

    public function curriculumMaterials(Program $program): View
    {
        abort_unless($program->type === 'internship', 404);
        abort_unless($program->mentor_id === auth()->id(), 403);
        $program->ensureInternshipWeeks();

        $module = $program->modules->first();
        if (!$module) {
            $module = $program->modules()->create(['title' => 'Minggu 1', 'sort_order' => 1])->first();
        }

        $lessons = $module->lessons()->latest()->get();

        return view('mentor.internships.curriculum_materials', compact('program', 'module', 'lessons'));
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

        $program = Program::create([
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

        notify_admins(
            'Bootcamp menunggu approval',
            auth()->user()->name.' mengajukan '.$program->title.'.',
            'info',
            route('admin.programs.index', ['type' => 'bootcamp'])
        );

        return redirect()->route('mentor.programs.index')->with('success', 'Bootcamp diajukan. Menunggu approve admin.');
    }

    public function edit(Program $program): View
    {
        abort_unless($program->mentor_id === auth()->id(), 403);
        abort_unless($program->type === 'bootcamp', 404);

        return view('mentor.programs.form', [
            'program' => $program,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Program $program): RedirectResponse
    {
        abort_unless($program->mentor_id === auth()->id(), 403);
        abort_unless($program->type === 'bootcamp', 404);

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

        $wasRejected = $program->approval_status === 'rejected';

        $program->update([
            'title' => $data['title'],
            'level' => $data['level'],
            'duration_months' => $data['duration_months'],
            'price' => $data['price'],
            'excerpt' => $data['excerpt'] ?? null,
            'description' => $data['description'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'benefits' => collect(preg_split('/\r\n|\r|\n/', $data['benefits_text'] ?? ''))
                ->map(fn ($l) => trim($l))->filter()->values()->all(),
            'approval_status' => $wasRejected ? 'pending' : $program->approval_status,
            'is_published' => $wasRejected ? false : $program->is_published,
        ]);

        if ($wasRejected) {
            notify_admins(
                'Bootcamp diajukan ulang',
                auth()->user()->name.' memperbaiki '.$program->title.'.',
                'info',
                route('admin.programs.index', ['type' => 'bootcamp'])
            );
        }

        return redirect()->route('mentor.programs.index')->with('success', $wasRejected
            ? 'Perubahan disimpan dan diajukan ulang ke admin.'
            : 'Bootcamp diperbarui.');
    }

    public function destroyModule(Module $module): RedirectResponse
    {
        abort_unless($module->program->mentor_id === auth()->id(), 403);

        try {
            $module->load('lessons.assignment');
            foreach ($module->lessons as $lesson) {
                $lesson->assignment?->questions()->delete();
                $lesson->assignment()?->delete();
                $lesson->delete();
            }
            $module->delete();
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Gagal menghapus minggu: '.$e->getMessage());
        }

        return back()->with('success', 'Minggu/modul dihapus.');
    }

    public function destroyLesson(\App\Models\Lesson $lesson): RedirectResponse
    {
        $program = $lesson->module->program;
        abort_unless($program->mentor_id === auth()->id(), 403);

        if ($program->type === 'internship' && $lesson->type === 'assignment') {
            return back()->with('error', 'Slot pengumpulan tugas selalu ada di setiap minggu dan tidak bisa dihapus. Kosongkan instruksinya kalau minggu ini tanpa tugas.');
        }

        try {
            $lesson->load('assignment');
            $lesson->assignment?->questions()->delete();
            $lesson->assignment()?->delete();
            $lesson->delete();
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Gagal menghapus materi: '.$e->getMessage());
        }

        return back()->with('success', 'Materi dihapus.');
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

        $label = $program->type === 'internship' ? 'Minggu ditambahkan.' : 'Modul ditambahkan.';

        return back()->with('success', $label);
    }

    public function updateModule(Request $request, Module $module): RedirectResponse
    {
        abort_unless($module->program->mentor_id === auth()->id(), 403);
        $data = $request->validate(['title' => ['required', 'string', 'max:160']]);
        $module->update(['title' => $data['title']]);

        return back()->with('success', 'Nama minggu diperbarui.');
    }

    public function storeLesson(Request $request, Module $module): RedirectResponse
    {
        abort_unless($module->program->mentor_id === auth()->id(), 403);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'type' => ['required', 'in:video,text,quiz,pdf,article,recording,assignment'],
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

        if ($data['type'] === 'assignment') {
            $lesson->assignment()->create([
                'title' => $data['title'],
                'instructions' => $data['instructions'] ?? null,
                'deadline' => $data['deadline'] ?? null,
                'kind' => 'assignment',
            ]);

            $this->notifyInternshipStudents(
                $module->program,
                'Tugas magang baru',
                auth()->user()->name.' menambahkan "'.$lesson->title.'" di '.$module->title.'.',
                $lesson
            );

            return back()->with('success', 'Tugas ditambahkan. Peserta magang langsung melihatnya di ruang belajar dan mengumpulkan lewat tautan.');
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

            $this->notifyInternshipStudents(
                $module->program,
                'Quiz magang baru',
                auth()->user()->name.' menambahkan "'.$lesson->title.'" di '.$module->title.'.',
                $lesson
            );

            return back()->with('success', "Quiz ditambahkan ({$saved} soal). Peserta langsung melihatnya di ruang belajar.");
        }

        $this->notifyInternshipStudents(
            $module->program,
            'Materi magang baru',
            auth()->user()->name.' menambahkan "'.$lesson->title.'" di '.$module->title.'.',
            $lesson
        );

        return back()->with('success', 'Materi ditambahkan. Peserta magang langsung melihatnya di ruang belajar.');
    }

    private function notifyInternshipStudents(Program $program, string $title, string $body, ?\App\Models\Lesson $lesson = null): void
    {
        if ($program->type !== 'internship') {
            return;
        }

        $link = $lesson
            ? route('learn.lesson', [$program, $lesson])
            : route('learn.show', $program);

        Enrollment::query()
            ->where('program_id', $program->id)
            ->whereIn('status', ['active', 'completed'])
            ->pluck('user_id')
            ->unique()
            ->each(fn ($userId) => AppNotification::create([
                'user_id' => $userId,
                'title' => $title,
                'body' => $body,
                'type' => 'info',
                'link' => $link,
            ]));
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

    private function validatedInternship(Request $request, ?Program $program = null): array
    {
        $minQuota = $program ? max(1, $program->acceptedInternCount()) : 1;

        return $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'education_level' => ['required', 'string', 'max:40'],
            'majors' => ['nullable', 'string', 'max:1000'],
            'division' => ['nullable', 'string', 'max:120'],
            'location' => ['nullable', 'string', 'max:255'],
            'deadline' => ['nullable', 'date'],
            'duration_months' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'quota' => ['required', 'integer', 'min:'.$minQuota, 'max:500'],
        ], [
            'quota.min' => 'Kuota tidak boleh lebih kecil dari jumlah peserta yang sudah diterima ('.$minQuota.').',
        ]);
    }

    private function parseLines(?string $text): array
    {
        if (! $text) {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n/', $text))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }
}
