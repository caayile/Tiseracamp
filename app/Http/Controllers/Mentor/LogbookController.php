<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\LogbookEntry;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LogbookController extends Controller
{
    public function index(Request $request): View
    {
        $programIds = $this->accessibleProgramIds();
        $programId = $request->integer('program_id') ?: null;
        $search = trim($request->string('q')->toString());

        if ($programId && ! $programIds->contains($programId)) {
            abort(403);
        }

        $programs = Program::query()
            ->whereIn('id', $programIds)
            ->orderBy('type')
            ->orderBy('title')
            ->get();

        $filterIds = $programId ? collect([$programId]) : $programIds;

        $participants = User::query()
            ->where('role', 'student')
            ->whereHas('enrollments', fn ($q) => $q->whereIn('program_id', $filterIds))
            ->withCount(['logbookEntries as entries_count' => fn ($q) => $q->whereIn('program_id', $filterIds)])
            ->withSum(['logbookEntries as total_hours' => fn ($q) => $q->whereIn('program_id', $filterIds)], 'hours')
            ->with([
                'enrollments' => fn ($q) => $q->whereIn('program_id', $filterIds)->with('program:id,title,type'),
                'logbookEntries' => fn ($q) => $q->whereIn('program_id', $filterIds)->latest('entry_date'),
            ])
            ->when($search, fn ($q) => $q->where(function ($sq) use ($search) {
                $needle = '%'.mb_strtolower($search).'%';
                $sq->whereRaw('LOWER(name) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$needle]);
            }))
            ->orderByDesc('entries_count')
            ->paginate(20)
            ->withQueryString();

        return view('mentor.logbooks.index', [
            'participants' => $participants,
            'programs' => $programs,
            'programId' => $programId,
            'search' => $search,
        ]);
    }

    public function show(Request $request, User $user): View
    {
        abort_unless($user->role === 'student', 404);

        $programIds = $this->accessibleProgramIds();
        abort_unless(
            $user->enrollments()->whereIn('program_id', $programIds)->exists(),
            403
        );

        $programId = $request->integer('program_id') ?: null;
        if ($programId && ! $programIds->contains($programId)) {
            abort(403);
        }

        $studentProgramIds = $user->enrollments()
            ->whereIn('program_id', $programIds)
            ->pluck('program_id');

        $entries = LogbookEntry::with('program')
            ->where('user_id', $user->id)
            ->whereIn('program_id', $studentProgramIds)
            ->when($programId, fn ($q) => $q->where('program_id', $programId))
            ->latest('entry_date')
            ->paginate(30)
            ->withQueryString();

        $programs = Program::query()
            ->whereIn('id', $studentProgramIds)
            ->orderBy('title')
            ->get();

        $program = $programId
            ? $programs->firstWhere('id', $programId)
            : ($programs->first() ?? $entries->first()?->program);

        return view('mentor.logbooks.show', [
            'user' => $user,
            'entries' => $entries,
            'programs' => $programs,
            'programId' => $programId,
            'program' => $program,
        ]);
    }

    public function exportExcel(Request $request, User $user): StreamedResponse|RedirectResponse
    {
        abort_unless($user->role === 'student', 404);

        $programIds = $this->accessibleProgramIds();
        abort_unless(
            $user->enrollments()->whereIn('program_id', $programIds)->exists(),
            403
        );

        $programId = $request->integer('program_id') ?: null;
        if ($programId && ! $programIds->contains($programId)) {
            abort(403);
        }

        $studentProgramIds = $user->enrollments()
            ->whereIn('program_id', $programIds)
            ->pluck('program_id');

        $entries = LogbookEntry::with('program')
            ->where('user_id', $user->id)
            ->whereIn('program_id', $studentProgramIds)
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
                'Program',
                'Tipe',
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
                    $entry->program?->typeLabel() ?? '',
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
        abort_unless($this->accessibleProgramIds()->contains($logbook->program_id), 403);

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
            'Mentor meninjau entri "'.$logbook->title.'".',
            $data['status'] === 'revision' ? 'warning' : 'info',
            route('profile.logbook')
        );

        return back()->with('success', 'Review logbook disimpan.');
    }

    /** @return Collection<int, int> */
    private function accessibleProgramIds(): Collection
    {
        return Program::query()
            ->where('mentor_id', auth()->id())
            ->whereIn('type', ['internship', 'bootcamp'])
            ->pluck('id');
    }
}
