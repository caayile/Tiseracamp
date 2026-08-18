<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogbookEntry;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
            ->paginate(30)
            ->withQueryString();

        $programs = Program::query()
            ->where('type', 'internship')
            ->whereHas('enrollments', fn ($q) => $q->where('user_id', $user->id))
            ->orderBy('title')
            ->get();

        $vacancy = $programId
            ? $programs->firstWhere('id', $programId)
            : ($programs->first() ?? $entries->first()?->program);

        return view('admin.logbooks.show', [
            'user' => $user,
            'entries' => $entries,
            'programs' => $programs,
            'programId' => $programId,
            'vacancy' => $vacancy,
        ]);
    }

    public function exportExcel(Request $request, User $user): StreamedResponse|RedirectResponse
    {
        abort_unless($user->role === 'student', 404);

        $programId = $request->integer('program_id') ?: null;

        $entries = LogbookEntry::with('program')
            ->where('user_id', $user->id)
            ->when($programId, fn ($q) => $q->where('program_id', $programId))
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get();

        if ($entries->isEmpty()) {
            return back()->with('error', 'Belum ada entri logbook untuk diexport.');
        }

        $filename = 'logbook-'.$user->id.'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($user, $entries) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Nama Peserta',
                'Email',
                'Lowongan',
                'Tanggal',
                'Aktivitas',
                'Kendala',
                'Progress (%)',
                'Status',
                'Jam Kerja',
            ]);

            foreach ($entries as $entry) {
                fputcsv($handle, [
                    $user->name,
                    $user->email,
                    $entry->program?->title ?? '',
                    $entry->entry_date?->format('Y-m-d') ?? '',
                    trim($entry->title.($entry->body ? ' — '.$entry->body : '')),
                    $entry->obstacles ?: '-',
                    $entry->progressPercent(),
                    $entry->workStatusLabel(),
                    $entry->hours,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function review(Request $request, LogbookEntry $logbook): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:reviewed,revision,submitted'],
            'reviewer_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $logbook->update([
            'status' => $data['status'],
            'reviewer_note' => $data['reviewer_note'] ?? null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        notify_user(
            $logbook->user_id,
            'Logbook direview',
            'Admin meninjau entri "'.$logbook->title.'".',
            $data['status'] === 'revision' ? 'warning' : 'info',
            route('profile.logbook')
        );

        return back()->with('success', 'Review logbook disimpan.');
    }

    public function attachment(LogbookEntry $logbook): BinaryFileResponse|RedirectResponse
    {
        return $logbook->serveAttachment();
    }
}
