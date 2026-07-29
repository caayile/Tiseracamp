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
            'program' => new Program,
            'partners' => Partner::orderBy('name')->get(),
            'mentors' => User::where('role', 'mentor')->orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['title']).'-'.Str::random(4);
        $data['benefits'] = $this->parseBenefits($request->input('benefits_text'));
        $data['is_published'] = $request->boolean('is_published');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['approval_status'] = $request->input('approval_status', 'approved');

        Program::create($data);

        return redirect()->route('admin.programs.index')->with('success', 'Program berhasil dibuat.');
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
        $data = $this->validated($request);
        $data['benefits'] = $this->parseBenefits($request->input('benefits_text'));
        $data['is_published'] = $request->boolean('is_published');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['approval_status'] = $request->input('approval_status', $program->approval_status);

        $program->update($data);

        return redirect()->route('admin.programs.index')->with('success', 'Program diperbarui.');
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
            'video_url' => ['nullable', 'url'],
            'file_url' => ['nullable', 'url'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
        ]);

        $module->lessons()->create([
            ...$data,
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
