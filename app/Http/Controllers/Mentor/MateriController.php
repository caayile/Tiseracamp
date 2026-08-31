<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\QuizQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

        $data = $request->validate($this->lessonRules());
        $mediaError = $this->validateRequiredMedia($request, $data['type']);
        if ($mediaError) {
            return back()->withInput()->withErrors($mediaError);
        }

        $attributes = $this->lessonAttributes($request, $data, new Lesson);
        $attributes['sort_order'] = $module->lessons()->count() + 1;
        $lesson = $module->lessons()->create($attributes);

        return $this->syncRelatedAndRespond($request, $data, $lesson, created: true);
    }

    public function edit(Lesson $lesson): View
    {
        abort_unless($lesson->module->program->mentor_id === auth()->id(), 403);

        $lesson->load(['assignment.questions']);
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

        $data = $request->validate($this->lessonRules());
        $mediaError = $this->validateRequiredMedia($request, $data['type'], $lesson);
        if ($mediaError) {
            return back()->withInput()->withErrors($mediaError);
        }

        $lesson->update($this->lessonAttributes($request, $data, $lesson));

        return $this->syncRelatedAndRespond($request, $data, $lesson, created: false);
    }

    public function destroy(Lesson $lesson): RedirectResponse
    {
        abort_unless($lesson->module->program->mentor_id === auth()->id(), 403);

        $lesson->load('assignment');
        $lesson->assignment?->questions()->delete();
        $lesson->assignment()?->delete();
        $this->deleteStoredPath($lesson->file_url);
        $this->deleteStoredPath($lesson->image_path);
        $lesson->delete();

        return back()->with('success', 'Materi dihapus.');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function lessonRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:text,video,article,pdf,recording,quiz,assignment'],
            'content' => ['nullable', 'string'],
            'video_url' => ['nullable', 'string', 'max:2048'],
            'file_url' => ['nullable', 'string', 'max:2048'],
            'audio_url' => ['nullable', 'string', 'max:2048'],
            'pdf_file' => ['nullable', 'file', 'mimes:pdf', 'max:15360'],
            'video_file' => ['nullable', 'file', 'max:51200'],
            'audio_file' => ['nullable', 'file', 'max:20480'],
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
        ];
    }

    /**
     * @return array<string, string>|null
     */
    private function validateRequiredMedia(Request $request, string $type, ?Lesson $existing = null): ?array
    {
        if ($type === 'pdf'
            && ! $request->hasFile('pdf_file')
            && blank($request->input('file_url'))
            && blank($existing?->file_url)
        ) {
            return ['pdf_file' => 'Upload file PDF atau tempel link PDF.'];
        }

        if ($type === 'video'
            && blank($request->input('video_url'))
            && ! $request->hasFile('video_file')
            && blank($existing?->video_url)
            && ! ($existing?->file_type === 'video' && filled($existing->file_url))
        ) {
            return ['video_url' => 'Tempel link YouTube atau unggah file video.'];
        }

        if ($type === 'recording'
            && ! $request->hasFile('audio_file')
            && blank($request->input('audio_url'))
            && blank($existing?->file_url)
            && blank($existing?->video_url)
        ) {
            return ['audio_file' => 'Unggah file audio atau tempel link rekaman.'];
        }

        return $this->assertUploadExtension($request, 'video_file', ['mp4', 'webm', 'mov'], 'Format video harus MP4, WebM, atau MOV.')
            ?? $this->assertUploadExtension($request, 'audio_file', ['mp3', 'wav', 'm4a', 'aac', 'ogg', 'mpeg', 'mpga'], 'Format audio harus MP3, WAV, M4A, AAC, atau OGG.');
    }

    /**
     * @param  list<string>  $allowed
     * @return array<string, string>|null
     */
    private function assertUploadExtension(Request $request, string $field, array $allowed, string $message): ?array
    {
        if (! $request->hasFile($field)) {
            return null;
        }

        $ext = strtolower((string) $request->file($field)->getClientOriginalExtension());
        if (! in_array($ext, $allowed, true)) {
            return [$field => $message];
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function lessonAttributes(Request $request, array $data, Lesson $lesson): array
    {
        $type = $data['type'];
        $content = null;

        if (in_array($type, ['text', 'article'], true)) {
            $content = $this->sanitizeRichText($data['content'] ?? null);
        } elseif (in_array($type, ['pdf', 'video'], true)) {
            $description = trim((string) ($data['description'] ?? ''));
            $content = $description !== '' ? nl2br(e($description)) : null;
        }

        $imagePath = $lesson->image_path;
        if ($request->hasFile('image') && in_array($type, ['text', 'article'], true)) {
            $this->deleteStoredPath($imagePath);
            $imagePath = $request->file('image')->store('lesson-images', media_disk());
        }

        [$fileUrl, $fileType] = $this->resolveFile($request, $data, $lesson);
        $videoUrl = null;
        if ($type === 'video') {
            $videoUrl = filled($data['video_url'] ?? null)
                ? $data['video_url']
                : ($request->hasFile('video_file') ? null : $lesson->getRawOriginal('video_url'));
        } elseif ($type === 'recording') {
            $videoUrl = ($request->hasFile('audio_file') || filled($data['audio_url'] ?? null))
                ? null
                : $lesson->getRawOriginal('video_url');
        }

        return [
            'title' => $data['title'],
            'type' => $type,
            'content' => $content,
            'video_url' => $videoUrl,
            'file_url' => $fileUrl,
            'file_type' => $fileType,
            'image_path' => $imagePath,
            'duration_minutes' => $data['duration_minutes'] ?? ($type === 'text' ? 10 : 15),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{0: ?string, 1: ?string}
     */
    private function resolveFile(Request $request, array $data, Lesson $lesson): array
    {
        $type = $data['type'];
        $fileUrl = $lesson->file_url;
        $fileType = $lesson->file_type;

        if ($type === 'pdf') {
            if ($request->hasFile('pdf_file')) {
                $this->deleteStoredPath($fileUrl);
                $fileUrl = $request->file('pdf_file')->store('lesson-pdfs', media_disk());
                $fileType = 'pdf';
            } elseif (filled($data['file_url'] ?? null)) {
                $this->deleteStoredPath($fileUrl);
                $fileUrl = $this->normalizeExternalUrl($data['file_url']);
                $fileType = str_contains(strtolower((string) $fileUrl), '.pdf') ? 'pdf' : 'pdf';
            }
        } elseif ($type === 'video') {
            if ($request->hasFile('video_file')) {
                $this->deleteStoredPath($fileUrl);
                $fileUrl = $request->file('video_file')->store('lesson-videos', media_disk());
                $fileType = 'video';
            }
        } elseif ($type === 'recording') {
            if ($request->hasFile('audio_file')) {
                $this->deleteStoredPath($fileUrl);
                $fileUrl = $request->file('audio_file')->store('lesson-audios', media_disk());
                $fileType = 'audio';
            } elseif (filled($data['audio_url'] ?? null)) {
                $this->deleteStoredPath($fileUrl);
                $fileUrl = $this->normalizeExternalUrl($data['audio_url']);
                $fileType = 'audio';
            }
        } else {
            $fileUrl = $lesson->exists ? $fileUrl : null;
            $fileType = $lesson->exists ? $fileType : null;
        }

        return [$fileUrl, $fileType];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncRelatedAndRespond(Request $request, array $data, Lesson $lesson, bool $created): RedirectResponse
    {
        if ($data['type'] === 'assignment') {
            $lesson->assignment()->updateOrCreate(
                ['lesson_id' => $lesson->id],
                [
                    'title' => $data['title'],
                    'instructions' => $data['instructions'] ?? null,
                    'deadline' => $data['deadline'] ?? null,
                    'kind' => 'assignment',
                ]
            );

            $message = $created
                ? 'Tugas ditambahkan. Peserta magang langsung melihatnya dan mengumpulkan lewat tautan.'
                : 'Tugas diperbarui, termasuk instruksi dan deadline.';

            return back()->with('success', $message);
        }

        if ($data['type'] === 'quiz') {
            $assignment = $lesson->assignment()->updateOrCreate(
                ['lesson_id' => $lesson->id],
                [
                    'title' => $data['title'],
                    'instructions' => $data['instructions'] ?? null,
                    'kind' => 'quiz',
                    'deadline' => $data['deadline'] ?? $lesson->assignment?->deadline,
                ]
            );

            $saved = $this->syncQuizQuestions($assignment, $data['questions'] ?? []);
            if ($saved === 0) {
                if ($created) {
                    $assignment->delete();
                    $lesson->delete();
                }

                return back()
                    ->withInput()
                    ->withErrors(['questions' => 'Quiz minimal 1 soal dengan pertanyaan dan 2 opsi.']);
            }

            $verb = $created ? 'ditambahkan' : 'diperbarui';

            return back()->with('success', "Quiz {$verb} ({$saved} soal).");
        }

        return back()->with('success', $created ? 'Materi ditambahkan. Peserta magang langsung melihatnya di ruang belajar.' : 'Materi diperbarui.');
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function syncQuizQuestions(Assignment $assignment, array $rows): int
    {
        $payload = [];
        foreach ($rows as $row) {
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

            $payload[] = [
                'question' => $question,
                'options' => $options,
                'correct_index' => $correct,
                'points' => 10,
            ];
        }

        if ($payload === []) {
            return $assignment->questions()->count();
        }

        $assignment->questions()->delete();
        foreach ($payload as $item) {
            QuizQuestion::create([
                'assignment_id' => $assignment->id,
                ...$item,
            ]);
        }

        return count($payload);
    }

    private function normalizeExternalUrl(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (! preg_match('~^https?://~i', $value)) {
            $value = 'https://'.$value;
        }

        return $value;
    }

    private function deleteStoredPath(?string $path): void
    {
        if (! filled($path) || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        try {
            Storage::disk(media_disk())->delete($path);
        } catch (\Throwable) {
        }
    }

    private function sanitizeRichText(?string $html): ?string
    {
        if (! filled($html)) {
            return null;
        }

        $html = strip_tags($html, '<div><p><br><strong><b><em><i><u><s><strike><span><font><blockquote><pre><code><a><img><ul><ol><li>');
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
            if ($element === $root) {
                continue;
            }

            foreach (iterator_to_array($element->attributes ?? []) as $attribute) {
                $keep = false;

                if ($element->tagName === 'font'
                    && $attribute->name === 'size'
                    && preg_match('/^[1-6]$/', $attribute->value)) {
                    $keep = true;
                } elseif ($element->tagName === 'a'
                    && $attribute->name === 'href'
                    && $this->isSafeHref($attribute->value)) {
                    $keep = true;
                } elseif ($element->tagName === 'img') {
                    $keep = ($attribute->name === 'alt')
                        || ($attribute->name === 'src' && $this->isSafeImageSrc($attribute->value));
                }

                if (! $keep) {
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

    private function isSafeHref(string $url): bool
    {
        $url = trim($url);
        if ($url === '' || str_starts_with($url, '#')) {
            return true;
        }

        if (preg_match('/^[a-zA-Z][a-zA-Z0-9+.\-]*:/', $url, $match)) {
            return in_array(strtolower($match[0]), ['http:', 'https:', 'mailto:', 'tel:'], true);
        }

        return true;
    }

    private function isSafeImageSrc(string $src): bool
    {
        $src = trim($src);
        if ($src === '') {
            return false;
        }

        if (str_starts_with($src, 'data:')) {
            return str_starts_with($src, 'data:image/');
        }

        if (preg_match('/^[a-zA-Z][a-zA-Z0-9+.\-]*:/', $src, $match)) {
            return in_array(strtolower($match[0]), ['http:', 'https:'], true);
        }

        return true;
    }
}
