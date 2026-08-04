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
        $programIds = Program::query()
            ->where('mentor_id', $mentorId)
            ->where('type', 'bootcamp')
            ->pluck('id');

        $pendingSubmissions = 0;
        if ($programIds->isNotEmpty()) {
            $pendingSubmissions = Submission::query()
                ->where('status', 'submitted')
                ->whereHas('assignment.lesson.module', fn ($q) => $q->whereIn('program_id', $programIds))
                ->count();
        }

        return view('mentor.dashboard', [
            'stats' => [
                'programs' => $programIds->count(),
                'students' => $programIds->isEmpty()
                    ? 0
                    : Enrollment::whereIn('program_id', $programIds)->count(),
                'submissions' => $pendingSubmissions,
                'rating' => auth()->user()->rating,
            ],
            'programs' => Program::query()
                ->where('mentor_id', $mentorId)
                ->where('type', 'bootcamp')
                ->withCount('enrollments')
                ->latest()
                ->get(),
            'upcoming' => ClassSchedule::query()
                ->where('mentor_id', $mentorId)
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->take(5)
                ->get(),
        ]);
    }
}
