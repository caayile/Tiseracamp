<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogbookEntry;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogbookController extends Controller
{
    public function index(Request $request): View
    {
        $programId = $request->integer('program_id') ?: null;
        $search = trim($request->string('q')->toString());

        $programs = Program::query()
            ->where('type', 'internship')
            ->orderBy('title')
            ->get();

        $participants = User::query()
            ->where('role', 'student')
            ->withCount(['logbookEntries as entries_count'])
            ->withSum(['logbookEntries as total_hours'], 'hours')
            ->with([
                'enrollments.program' => fn ($q) => $q->where('type', 'internship'),
                'logbookEntries' => fn ($q) => $q->latest('entry_date'),
            ])
            ->whereHas('enrollments.program', fn ($q) => $q->where('type', 'internship'))
            ->when($programId, fn ($q) => $q->whereHas('enrollments', fn ($eq) => $eq->where('program_id', $programId)))
            ->when($search, fn ($q) => $q->where(function ($sq) use ($search) {
                $needle = '%'.mb_strtolower($search).'%';
                $sq->whereRaw('LOWER(name) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$needle]);
            }))
            ->orderByDesc('entries_count')
            ->paginate(20)
            ->withQueryString();

        return view('admin.logbooks.index', [
            'participants' => $participants,
            'programs' => $programs,
            'programId' => $programId,
            'search' => $search,
        ]);
    }

    public function show(Request $request, User $user): View
    {
        abort_unless($user->role === 'student', 404);

        $programId = $request->integer('program_id') ?: null;

        $entries = LogbookEntry::with('program')
            ->where('user_id', $user->id)
            ->when($programId, fn ($q) => $q->where('program_id', $programId))
            ->latest('entry_date')
            ->paginate(20)
            ->withQueryString();

        $programs = Program::query()
            ->where('type', 'internship')
            ->orderBy('title')
            ->get();

        $totalHours = LogbookEntry::where('user_id', $user->id)
            ->when($programId, fn ($q) => $q->where('program_id', $programId))
            ->sum('hours');

        $programCount = $user->logbookEntries()
            ->distinct('program_id')
            ->count('program_id');

        return view('admin.logbooks.show', [
            'user' => $user,
            'entries' => $entries,
            'programs' => $programs,
            'programId' => $programId,
            'totalHours' => $totalHours,
            'programCount' => $programCount,
        ]);
    }
}
