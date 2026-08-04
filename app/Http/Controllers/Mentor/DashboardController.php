<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\ClassSchedule;
use App\Models\Enrollment;
use App\Models\Program;
use App\Models\Submission;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $mentorId = auth()->id();
        $programIds = Program::where('mentor_id', $mentorId)->where('type', 'bootcamp')->pluck('id');

        return view('mentor.dashboard', [
            'stats' => [
                'programs' => $programIds->count(),
                'students' => Enrollment::whereIn('program_id', $programIds)->count(),
                'submissions' => Submission::whereHas('assignment.lesson.module', fn ($q) => $q->whereIn('program_id', $programIds))
                    ->where('status', 'submitted')->count(),
                'rating' => auth()->user()->rating,
            ],
            'programs' => Program::where('mentor_id', $mentorId)
                ->where('type', 'bootcamp')
                ->withCount('enrollments')
                ->latest()
                ->get(),
            'upcoming' => ClassSchedule::where('mentor_id', $mentorId)->where('starts_at', '>=', now())->orderBy('starts_at')->take(5)->get(),
        ]);
    }
}
