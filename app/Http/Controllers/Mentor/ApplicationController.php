<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\InternshipApplication;
use App\Models\Program;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function index(): View
    {
        $programIds = Program::query()
            ->where('mentor_id', auth()->id())
            ->where('type', 'internship')
            ->pluck('id');

        $applications = InternshipApplication::query()
            ->with(['user', 'program'])
            ->whereIn('program_id', $programIds)
            ->latest()
            ->paginate(20);

        return view('mentor.applications.index', compact('applications'));
    }
}
