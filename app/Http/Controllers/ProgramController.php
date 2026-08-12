<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\InternshipApplication;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgramController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->string('type')->toString();
        // Halaman katalog dipisah: default bootcamp, ?type=internship untuk magang
        $catalogType = $type === 'internship' ? 'internship' : 'bootcamp';

        $query = Program::published()
            ->with(['partner', 'mentor', 'category'])
            ->where('type', $catalogType);

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

        $programs = $query->latest()->paginate(9)->withQueryString();

        return view('programs.index', compact('programs', 'catalogType'));
    }

    public function show(string $slug): View
    {
        $program = Program::published()
            ->with(['partner', 'mentor', 'category'])
            ->where('slug', $slug)
            ->firstOrFail();

        abort_unless($program->isVisibleTo(auth()->user()), 404);

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
