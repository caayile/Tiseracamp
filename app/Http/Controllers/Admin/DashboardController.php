<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Program;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $roleCounts = User::query()
            ->select('role', DB::raw('count(*) as total'))
            ->whereIn('role', ['student', 'mentor'])
            ->groupBy('role')
            ->pluck('total', 'role');

        $totalStudents = (int) ($roleCounts['student'] ?? 0);
        $tsuStudents = User::where('role', 'student')->where('is_tsu', true)->count();

        $enrollmentStats = Enrollment::query()
            ->selectRaw("count(*) as total")
            ->selectRaw("count(*) filter (where status = 'active') as active")
            ->selectRaw("count(*) filter (where status = 'completed') as completed")
            ->first();

        $totalEnrollments = (int) ($enrollmentStats->total ?? 0);
        $completed = (int) ($enrollmentStats->completed ?? 0);

        return view('admin.dashboard', [
            'stats' => [
                'users' => $totalStudents,
                'mentors' => (int) ($roleCounts['mentor'] ?? 0),
                'programs' => Program::count(),
                'active_enrollments' => (int) ($enrollmentStats->active ?? 0),
                'revenue' => (float) Payment::where('status', 'paid')->sum('amount'),
                'completion_rate' => $totalEnrollments ? round(($completed / $totalEnrollments) * 100) : 0,
            ],
            'tsuStats' => [
                'tsu' => $tsuStudents,
                'non_tsu' => $totalStudents - $tsuStudents,
            ],
            'recentEnrollments' => Enrollment::with(['user:id,name,email', 'program:id,title'])
                ->latest()
                ->take(6)
                ->get(),
            'pendingPayments' => Payment::with(['user:id,name,email', 'program:id,title'])
                ->where('status', 'waiting_verification')
                ->latest()
                ->take(5)
                ->get(),
            'logs' => ActivityLog::with('user:id,name')
                ->latest()
                ->take(8)
                ->get(),
        ]);
    }
}
