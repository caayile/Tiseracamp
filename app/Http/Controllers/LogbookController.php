<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\LogbookEntry;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LogbookController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'program_id' => ['required', 'exists:programs,id'],
            'entry_date' => ['required', 'date'],
            'title' => ['required', 'string', 'max:160'],
            'body' => ['required', 'string', 'max:5000'],
            'obstacles' => ['nullable', 'string', 'max:5000'],
            'hours' => ['required', 'integer', 'min:1', 'max:24'],
            'progress' => ['required', 'integer', 'min:0', 'max:100'],
            'attachment' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $enrollment = Enrollment::where('user_id', auth()->id())
            ->where('program_id', $data['program_id'])
            ->firstOrFail();

        $program = Program::findOrFail($data['program_id']);
        abort_unless($program->type === 'internship', 403);

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('logbooks', media_disk());
        }

        LogbookEntry::create([
            'user_id' => auth()->id(),
            'program_id' => $program->id,
            'enrollment_id' => $enrollment->id,
            'entry_date' => $data['entry_date'],
            'title' => $data['title'],
            'body' => $data['body'],
            'obstacles' => $data['obstacles'] ?? null,
            'hours' => $data['hours'],
            'progress' => $data['progress'],
            'attachment_path' => $path,
        ]);

        award_achievement(auth()->user(), 'first_logbook');

        return redirect()
            ->route('profile.logbook')
            ->with('success', 'Entri logbook disimpan.');
    }

    public function destroy(LogbookEntry $logbook): RedirectResponse
    {
        abort_unless($logbook->user_id === auth()->id(), 403);
        $logbook->delete();

        return redirect()
            ->route('profile.logbook')
            ->with('success', 'Entri logbook dihapus.');
    }

    public function exportPdf(Request $request): View
    {
        [$user, $logbooks, $programFilter] = $this->exportData($request);

        return view('profile.logbook-print', [
            'user' => $user,
            'logbooks' => $logbooks,
            'programFilter' => $programFilter,
            'totalHours' => $logbooks->sum('hours'),
        ]);
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        [$user, $logbooks] = $this->exportData($request);

        $filename = 'logbook-'.$user->id.'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($user, $logbooks) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM agar Excel Windows membaca karakter dengan benar
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Nama',
                'Email',
                'Program',
                'Tanggal',
                'Judul Kegiatan',
                'Jam Kerja',
                'Progress (%)',
                'Status Pengerjaan',
                'Aktivitas',
                'Kendala',
            ]);

            foreach ($logbooks as $entry) {
                fputcsv($handle, [
                    $user->name,
                    $user->email,
                    $entry->program?->title ?? '',
                    $entry->entry_date?->format('Y-m-d') ?? '',
                    $entry->title,
                    $entry->hours,
                    $entry->progressPercent(),
                    $entry->workStatusLabel(),
                    $entry->body,
                    $entry->obstacles ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return array{0: \App\Models\User, 1: \Illuminate\Support\Collection<int, LogbookEntry>, 2: ?Program}
     */
    private function exportData(Request $request): array
    {
        $user = $request->user();

        $data = $request->validate([
            'program_id' => ['nullable', 'exists:programs,id'],
        ]);

        $query = LogbookEntry::with('program')
            ->where('user_id', $user->id)
            ->orderBy('entry_date')
            ->orderBy('id');

        $programFilter = null;
        if (! empty($data['program_id'])) {
            $programFilter = Program::findOrFail($data['program_id']);
            abort_unless($programFilter->type === 'internship', 403);
            $query->where('program_id', $programFilter->id);
        }

        $logbooks = $query->get();

        abort_if($logbooks->isEmpty(), 404, 'Belum ada entri logbook untuk diexport.');

        return [$user, $logbooks, $programFilter];
    }
}
