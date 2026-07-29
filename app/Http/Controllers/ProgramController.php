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
        $query = Program::published()->with(['partner', 'mentor', 'category']);

        if ($type = $request->string('type')->toString()) {
            $query->where('type', $type);
        }

        if ($level = $request->string('level')->toString()) {
            $query->where('level', $level);
        }

        if ($category = $request->string('category')->toString()) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $category));
        }

        if ($search = $request->string('q')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        $programs = $query->latest()->paginate(9)->withQueryString();

        return view('programs.index', compact('programs'));
    }

    public function show(string $slug): View
    {
        $program = Program::published()
            ->with(['partner', 'mentor', 'modules.lessons', 'category'])
            ->where('slug', $slug)
            ->firstOrFail();

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
