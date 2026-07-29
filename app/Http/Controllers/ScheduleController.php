<?php

namespace App\Http\Controllers;

use App\Models\ClassSchedule;
use App\Models\Enrollment;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(): View
    {
        $programIds = Enrollment::where('user_id', auth()->id())->pluck('program_id');
        $schedules = ClassSchedule::with(['program', 'mentor'])
            ->whereIn('program_id', $programIds)
            ->orderBy('starts_at')
            ->get();

        return view('schedules.index', compact('schedules'));
    }
}
