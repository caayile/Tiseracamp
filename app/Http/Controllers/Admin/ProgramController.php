<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Partner;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProgramController extends Controller
{
    public function index(): View
    {
        $programs = Program::with(['partner', 'mentor', 'category', 'batches'])->latest()->paginate(10);

        return view('admin.programs.index', compact('programs'));
    }

    public function create(): View
    {
        return view('admin.programs.form', [
            'program' => new Program(['type' => 'internship', 'level' => 'Beginner', 'price' => 0]),
            'partners' => Partner::orderBy('name')->get(),
            'mentors' => User::where('role', 'mentor')->orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'type' => 'internship',
            'level' => 'Beginner',
            'price' => 0,
            'is_featured' => false,
        ]);

        $data = $this->validated($request);
        $data['type'] = 'internship';
        $data['level'] = 'Beginner';
        $data['price'] = 0;
        $data['slug'] = Str::slug($data['title']).'-'.Str::random(4);
        $data['benefits'] = $this->parseBenefits($request->input('benefits_text'));
        $data['qualifications'] = $this->parseBenefits($request->input('qualifications_text'));
        $data['required_documents'] = $this->parseBenefits($request->input('required_documents_text'));
        $data['preferred_skills'] = $this->parseBenefits($request->input('preferred_skills_text'));
        $data['responsibilities'] = $this->parseBenefits($request->input('responsibilities_text'));
        $data['description'] = $request->input('description');
        $data['is_published'] = true;
        $data['is_open'] = true;
        $data['is_featured'] = false;
        $data['approval_status'] = 'approved';
        $data['mentor_id'] = null;
        $data['category_id'] = null;
        $data['partner_id'] = null;
        $data['excerpt'] = null;
        $data = $this->normalizeInternshipFields($data);

        Program::create($data);

        return redirect()->route('admin.programs.index')->with('success', 'Lowongan magang berhasil dibuat. Atur publikasi lewat tombol Publikasi.');
    }

    public function edit(Program $program): View
    {
        $program->load('batches');

        return view('admin.programs.form', [
            'program' => $program,
            'partners' => Partner::orderBy('name')->get(),
            'mentors' => User::where('role', 'mentor')->orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Program $program): RedirectResponse
    {
        $request->merge(['type' => $program->type]);

        if ($program->type === 'internship') {
            $request->merge([
                'level' => 'Beginner',
                'price' => 0,
            ]);
        }

        $data = $this->validated($request);
        $data['type'] = $program->type;

        if ($program->type === 'internship') {
            // Hanya update detail lowongan; publikasi di halaman terpisah
            $program->update([
                'title' => $data['title'],
                'education_level' => $data['education_level'] ?? null,
                'majors' => $data['majors'] ?? null,
                'division' => $data['division'] ?? null,
                'location' => $data['location'] ?? null,
                'deadline' => $data['deadline'] ?? null,
                'duration_months' => $data['duration_months'],
                'description' => $data['description'] ?? null,
                'qualifications' => $this->parseBenefits($request->input('qualifications_text')),
                'required_documents' => $this->parseBenefits($request->input('required_documents_text')),
                'preferred_skills' => $this->parseBenefits($request->input('preferred_skills_text')),
                'benefits' => $this->parseBenefits($request->input('benefits_text')),
                'responsibilities' => $this->parseBenefits($request->input('responsibilities_text')),
                'price' => 0,
                'level' => 'Beginner',
            ]);

            return redirect()->route('admin.programs.index')->with('success', 'Detail lowongan magang diperbarui.');
        }

        $data['benefits'] = $this->parseBenefits($request->input('benefits_text'));
        $data['is_published'] = $request->boolean('is_published');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['approval_status'] = $request->input('approval_status', $program->approval_status);
        $data = $this->normalizeInternshipFields($data);

        $program->update($data);

        return redirect()->route('admin.programs.index')->with('success', 'Program diperbarui.');
    }

    public function publikasi(Program $program): View
    {
        return view('admin.programs.publikasi', [
            'program' => $program,
            'partners' => Partner::orderBy('name')->get(),
            'mentors' => User::where('role', 'mentor')->orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function updatePublikasi(Request $request, Program $program): RedirectResponse
    {
        $data = $request->validate([
            'mentor_id' => ['nullable', 'exists:users,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'partner_id' => ['nullable', 'exists:partners,id'],
            'approval_status' => ['required', 'in:draft,pending,approved,rejected'],
            'excerpt' => ['nullable', 'string', 'max:300'],
            'description' => ['nullable', 'string'],
        ]);

        $payload = [
            'mentor_id' => $data['mentor_id'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'partner_id' => $data['partner_id'] ?? null,
            'approval_status' => $data['approval_status'],
            'excerpt' => $data['excerpt'] ?? null,
            'is_published' => $request->boolean('is_published'),
            'is_featured' => $program->type === 'bootcamp' ? $request->boolean('is_featured') : false,
        ];

        if ($program->type === 'bootcamp') {
            $payload['description'] = $data['description'] ?? null;
        }

        $program->update($payload);

        return redirect()->route('admin.programs.index')->with('success', 'Pengaturan publikasi disimpan.');
    }

    public function destroy(Program $program): RedirectResponse
    {
        $program->delete();

        return back()->with('success', 'Program dihapus.');
    }

    public function approve(Program $program): RedirectResponse
    {
        $program->update(['approval_status' => 'approved', 'is_published' => true]);

        return back()->with('success', 'Program disetujui & dipublish.');
    }

    public function toggleOpen(Program $program): RedirectResponse
    {
        abort_unless($program->type === 'internship', 404);

        $program->update(['is_open' => ! $program->is_open]);

        $label = $program->is_open ? 'dibuka' : 'ditutup';

        return back()->with('success', "Lowongan magang berhasil {$label}.");
    }

    public function curriculum(Program $program): View
    {
        $program->load(['modules.lessons', 'batches', 'mentor']);

        return view('admin.programs.curriculum', [
            'program' => $program,
            'mentors' => User::where('role', 'mentor')->orderBy('name')->get(),
        ]);
    }

    public function storeModule(Request $request, Program $program): RedirectResponse
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:160']]);
        $program->modules()->create([
            'title' => $data['title'],
            'sort_order' => $program->modules()->count() + 1,
        ]);

        return back()->with('success', 'Modul ditambahkan.');
    }

    public function storeLesson(Request $request, Module $module): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'type' => ['required', 'in:video,text,quiz,pdf,article,recording'],
            'content' => ['nullable', 'string'],
            'description' => ['nullable', 'string', 'max:5000'],
            'video_url' => ['nullable', 'url'],
            'file_url' => ['nullable', 'url'],
            'pdf_file' => ['nullable', 'file', 'mimes:pdf', 'max:15360'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if ($data['type'] === 'pdf' && ! $request->hasFile('pdf_file') && blank($data['file_url'] ?? null)) {
            return back()
                ->withInput()
                ->withErrors(['pdf_file' => 'Upload file PDF atau tempel link PDF.']);
        }

        $imagePath = null;
        if ($request->hasFile('image') && in_array($data['type'], ['text', 'article'], true)) {
            $imagePath = $request->file('image')->store('lesson-images', media_disk());
        }

        $content = $data['content'] ?? null;
        if ($data['type'] === 'pdf') {
            $description = trim((string) ($data['description'] ?? ''));
            $content = $description !== '' ? nl2br(e($description)) : null;
        }

        $fileUrl = $data['file_url'] ?? null;
        $fileType = null;
        if ($data['type'] === 'pdf' && $request->hasFile('pdf_file')) {
            $fileUrl = $request->file('pdf_file')->store('lesson-pdfs', media_disk());
            $fileType = 'pdf';
        } elseif (filled($fileUrl) && str_contains(strtolower($fileUrl), '.pdf')) {
            $fileType = 'pdf';
        }

        $module->lessons()->create([
            'title' => $data['title'],
            'type' => $data['type'],
            'content' => $content,
            'video_url' => $data['video_url'] ?? null,
            'file_url' => $fileUrl,
            'file_type' => $fileType,
            'image_path' => $imagePath,
            'duration_minutes' => $data['duration_minutes'] ?? 10,
            'sort_order' => $module->lessons()->count() + 1,
        ]);

        return back()->with('success', 'Lesson ditambahkan.');
    }

    public function destroyModule(Module $module): RedirectResponse
    {
        $module->delete();

        return back()->with('success', 'Modul dihapus.');
    }

    public function destroyLesson(Lesson $lesson): RedirectResponse
    {
        $lesson->delete();

        return back()->with('success', 'Lesson dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'type' => ['required', 'in:bootcamp,internship'],
            'level' => ['required', 'string', 'max:40'],
            'education_level' => ['nullable', 'string', 'max:40'],
            'majors' => ['nullable', 'string', 'max:1000'],
            'division' => ['nullable', 'string', 'max:120'],
            'location' => ['nullable', 'string', 'max:255'],
            'deadline' => ['nullable', 'date'],
            'duration_months' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'integer', 'min:0'],
            'excerpt' => ['nullable', 'string', 'max:300'],
            'description' => ['nullable', 'string'],
            'partner_id' => ['nullable', 'exists:partners,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'mentor_id' => ['nullable', 'exists:users,id'],
            'approval_status' => ['nullable', 'in:draft,pending,approved,rejected'],
        ]);
    }

    private function normalizeInternshipFields(array $data): array
    {
        if (($data['type'] ?? null) !== 'internship') {
            $data['education_level'] = null;
            $data['majors'] = null;
            $data['division'] = null;
            $data['location'] = null;
            $data['deadline'] = null;
            $data['qualifications'] = [];
            $data['required_documents'] = [];
            $data['preferred_skills'] = [];
            $data['responsibilities'] = [];

            return $data;
        }

        $data['price'] = $data['price'] ?? 0;

        return $data;
    }

    private function parseBenefits(?string $text): array
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
