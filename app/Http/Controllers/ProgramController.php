<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Enrollment;
use App\Models\InternshipApplication;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgramController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->string('type')->toString();
        // Halaman katalog dipisah: default bootcamp, ?type=internship untuk magang
        $catalogType = $type === 'internship' ? 'internship' : 'bootcamp';

        $isTsuStudent = auth()->user()?->isTsuStudent() ?? false;
        $scope = $isTsuStudent && $catalogType === 'internship' && $request->string('scope')->toString() === 'tsu'
            ? 'tsu'
            : 'all';

        $query = Program::published()
            ->with(['partner', 'mentor', 'category'])
            ->where('type', $catalogType);

        if ($catalogType === 'internship') {
            $query->forAudience($scope === 'tsu');
        }

        if ($category = $request->string('category')->toString()) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $category));
        }

        if ($search = trim($request->string('q')->toString())) {
            $needle = '%'.mb_strtolower($search).'%';
            $query->where(function ($q) use ($needle) {
                $q->whereRaw('LOWER(title) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(COALESCE(excerpt, \'\')) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(COALESCE(majors, \'\')) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(COALESCE(division, \'\')) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(COALESCE(location, \'\')) LIKE ?', [$needle]);
            });
        }

        $programs = ($catalogType === 'internship'
            ? $query->orderOpenFirst()
            : $query->latest()
        )->paginate(9)->withQueryString();
        $categories = Category::query()->orderBy('name')->get();
        $activeCategory = $request->string('category')->toString();

        return view('programs.index', compact('programs', 'catalogType', 'isTsuStudent', 'scope', 'categories', 'activeCategory'));
    }

    public function show(string $slug): View|RedirectResponse
    {
        $program = Program::published()
            ->with(['partner', 'mentor', 'category'])
            ->where('slug', $slug)
            ->firstOrFail();

        if (! $program->isVisibleTo(auth()->user())) {
            if (auth()->user()?->isTsuPending() && $program->isTsuOnly()) {
                return redirect()->route('dashboard')
                    ->with('error', 'Lowongan khusus TSU aktif setelah admin menyetujui KTM.');
            }

            abort(404);
        }

        // Dipakai navbar untuk highlight Magang vs Bootcamp tanpa query ekstra.
        view()->share('navProgramType', $program->type);

        $enrolled = false;
        $application = null;

        if (auth()->check()) {
            $enrolled = Enrollment::where('user_id', auth()->id())
                ->where('program_id', $program->id)
                ->exists();

            if ($program->type === 'internship') {
                $application = InternshipApplication::where('user_id', auth()->id())
                    ->where('program_id', $program->id)
                    ->first();
            }
        }

        return view('programs.show', compact('program', 'enrolled', 'application'));
    }
}
