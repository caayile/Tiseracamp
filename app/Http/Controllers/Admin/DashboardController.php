<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Program;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $paid = Payment::where('status', 'paid')->sum('amount');
        $enrollments = Enrollment::count();
        $completed = Enrollment::where('status', 'completed')->count();

        return view('admin.dashboard', [
            'stats' => [
                'users' => User::where('role', 'student')->count(),
                'mentors' => User::where('role', 'mentor')->count(),
                'programs' => Program::count(),
                'active_enrollments' => Enrollment::where('status', 'active')->count(),
                'revenue' => $paid,
                'completion_rate' => $enrollments ? round(($completed / $enrollments) * 100) : 0,
            ],
            'recentEnrollments' => Enrollment::with(['user', 'program'])->latest()->take(6)->get(),
            'pendingPayments' => Payment::with(['user', 'program'])->where('status', 'waiting_verification')->latest()->take(5)->get(),
            'logs' => ActivityLog::with('user')->latest()->take(8)->get(),
        ]);
    }
}
