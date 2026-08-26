<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Enrollment;
use App\Models\InternshipApplication;
use App\Models\Program;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class ApplicationController extends Controller
{
    public function index(): View
    {
        $filters = $this->filters();
        $query = $this->filteredQuery($filters);

        $applications = $query->paginate(10)->withQueryString();

        $rawCounts = $this->baseQuery($filters)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusCounts = collect([
            'pending' => (int) $rawCounts->get('submitted', 0) + (int) $rawCounts->get('under_review', 0),
            'accepted' => (int) $rawCounts->get('accepted', 0),
            'rejected' => (int) $rawCounts->get('rejected', 0),
        ]);

        $divisions = Program::query()
            ->where('type', 'internship')
            ->whereNotNull('division')
            ->where('division', '!=', '')
            ->distinct()
            ->orderBy('division')
            ->pluck('division');

        $filterProgram = $filters['program_id'] > 0
            ? Program::query()->find($filters['program_id'])
            : null;

        return view('admin.applications.index', [
            'applications' => $applications,
            'filterProgram' => $filterProgram,
            'divisions' => $divisions,
            'division' => $filters['division'],
            'search' => $filters['search'],
            'status' => $filters['status'],
            'statusCounts' => $statusCounts,
            'exportQuery' => request()->query(),
        ]);
    }

    public function exportSpreadsheet(): View|RedirectResponse|StreamedResponse|\Illuminate\Http\Response
    {
        $applications = $this->filteredQuery($this->filters())->get();

        if ($applications->isEmpty()) {
            return back()->with('error', 'Tidak ada pendaftar untuk dibuka di spreadsheet.');
        }

        $format = request()->query('format');

        if ($format === 'excel' || $format === 'xls') {
            $filename = 'rekap-pendaftar-'.now()->format('Ymd-His').'.xls';

            $html = view('admin.applications.excel', [
                'applications' => $applications,
            ])->render();

            return response($html, 200, [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
                'Cache-Control' => 'max-age=0',
            ]);
        }

        if ($format === 'csv') {
            $filename = 'rekap-pendaftar-'.now()->format('Ymd-His').'.csv';

            return response()->streamDownload(function () use ($applications) {
                $handle = fopen('php://output', 'w');
                fwrite($handle, "\xEF\xBB\xBF");

                fputcsv($handle, [
                    'No',
                    'Nama Pendaftar',
                    'Email',
                    'No. WhatsApp',
                    'Instansi / Perguruan Tinggi',
                    'Prodi / Jurusan',
                    'Jenjang',
                    'Semester',
                    'Lowongan',
                    'Divisi',
                    'Status',
                    'Mulai Magang',
                    'Selesai Magang',
                    'Tanggal Daftar',
                    'URL CV',
                    'URL Portofolio',
                ]);

                foreach ($applications as $index => $app) {
                    fputcsv($handle, [
                        $index + 1,
                        $app->displayName(),
                        $app->user?->email ?? '',
                        $app->phone ? "'".$app->phone : '',
                        $app->university ?? '',
                        $app->major ?? '',
                        $app->education_level ?? '',
                        $app->semester ?? '',
                        $app->program?->title ?? '',
                        $app->program?->division ?? '',
                        $app->statusLabel(),
                        $app->internship_start_date?->format('d/m/Y') ?? '',
                        $app->internship_end_date?->format('d/m/Y') ?? '',
                        $app->submitted_at?->format('d/m/Y H:i') ?? $app->created_at?->format('d/m/Y H:i') ?? '',
                        $app->documentUrl('cv') ?? '',
                        $app->portfolio_url ?: ($app->documentUrl('portfolio') ?? ''),
                    ]);
                }

                fclose($handle);
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        }

        return view('admin.applications.spreadsheet', [
            'applications' => $applications,
            'exportQuery' => request()->query(),
        ]);
    }

    public function exportZip(): BinaryFileResponse|RedirectResponse
    {
        if (! class_exists(ZipArchive::class)) {
            return back()->with('error', 'Ekstensi ZIP belum aktif di server.');
        }

        $applications = $this->filteredQuery($this->filters())->get();

        if ($applications->isEmpty()) {
            return back()->with('error', 'Tidak ada pendaftar untuk diunduh.');
        }

        $tempDir = storage_path('app/temp');
        if (! is_dir($tempDir)) {
            @mkdir($tempDir, 0755, true);
        }

        $zipPath = $tempDir.'/rekap-'.now()->format('YmdHis').'-'.bin2hex(random_bytes(4)).'.zip';

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'Gagal membuat arsip ZIP.');
        }

        $added = 0;
        foreach ($applications as $index => $application) {
            $folder = sprintf('%02d_%s', $index + 1, $application->fileSlug());

            foreach ($application->documentFiles() as $type => $file) {
                if (! filled($file['path'])) {
                    continue;
                }

                if (str_starts_with((string) $file['path'], 'http://') || str_starts_with((string) $file['path'], 'https://')) {
                    continue;
                }

                $absolute = resolve_public_upload($file['path']);
                if ($absolute === null) {
                    continue;
                }

                $extension = strtolower(pathinfo($absolute, PATHINFO_EXTENSION) ?: 'pdf');
                $zip->addFile($absolute, $folder.'/'.$application->documentFilename($type, $extension));
                $added++;
            }
        }

        $zip->close();

        if ($added === 0) {
            @unlink($zipPath);

            return back()->with('error', 'Berkas pendaftar tidak ditemukan di server.');
        }

        return response()->download($zipPath, 'berkas-pendaftar-magang-'.now()->format('Ymd').'.zip', [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    public function updateDates(Request $request, InternshipApplication $application): RedirectResponse|JsonResponse
    {
        abort_unless($application->program?->type === 'internship', 404);

        $data = $request->validate([
            'internship_start_date' => ['nullable', 'date'],
            'internship_end_date' => [
                'nullable',
                'date',
                Rule::when($request->filled('internship_start_date'), ['after_or_equal:internship_start_date']),
            ],
        ], [
            'internship_end_date.after_or_equal' => 'Tanggal selesai magang tidak boleh sebelum tanggal mulai.',
        ]);

        $application->update([
            'internship_start_date' => $data['internship_start_date'] ?: null,
            'internship_end_date' => $data['internship_end_date'] ?: null,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => 'Tanggal magang '.$application->displayName().' disimpan.',
            ]);
        }

        return back()->with('success', 'Tanggal magang '.$application->displayName().' disimpan.');
    }

    public function show(InternshipApplication $application): View
    {
        abort_unless($application->program?->type === 'internship', 404);

        $application->load(['user', 'program', 'reviewer']);

        return view('admin.applications.show', compact('application'));
    }

    public function review(Request $request, InternshipApplication $application): RedirectResponse
    {
        abort_unless($application->program?->type === 'internship', 404);

        $data = $request->validate([
            'status' => ['required', 'in:submitted,accepted,rejected,under_review'],
            'reviewer_note' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($application->status === $data['status']) {
            return back();
        }

        $application->update([
            'status' => $data['status'],
            'reviewer_note' => $data['reviewer_note'] ?? $application->reviewer_note,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        if ($data['status'] === 'accepted') {
            $application->loadMissing('program');
            if ($application->program && ! $application->program->hasAvailableSeat()) {
                return back()->with('error', 'Kuota batch magang ini sudah penuh. Buka batch baru dulu.');
            }

            Enrollment::firstOrCreate(
                ['user_id' => $application->user_id, 'program_id' => $application->program_id],
                [
                    'status' => 'active',
                    'progress' => 0,
                    'enrolled_at' => now(),
                    'batch_id' => $application->program?->enrollableBatchId(),
                ]
            );

            AppNotification::create([
                'user_id' => $application->user_id,
                'title' => 'Selamat! Diterima magang',
                'body' => 'Kamu diterima di '.$application->program->title.'. Silakan mulai onboarding.',
                'type' => 'success',
                'link' => route('learn.show', $application->program),
            ]);

            $application->loadMissing('user');
            if ($application->user) {
                award_achievement($application->user, 'internship_accepted');
                award_achievement($application->user, 'first_enrollment');
            }
        } elseif ($data['status'] === 'rejected') {
            AppNotification::create([
                'user_id' => $application->user_id,
                'title' => 'Hasil seleksi magang',
                'body' => 'Maaf, pendaftaran '.$application->program->title.' belum dapat diterima.',
                'type' => 'warning',
                'link' => route('internships.status', $application->program),
            ]);
        } else {
            AppNotification::create([
                'user_id' => $application->user_id,
                'title' => 'Pendaftaran sedang ditinjau',
                'body' => 'Admin sedang meninjau pendaftaran '.$application->program->title.'.',
                'type' => 'info',
                'link' => route('internships.status', $application->program),
            ]);
        }

        return back()->with('success', 'Status pendaftaran diperbarui.');
    }

    public function document(Request $request, InternshipApplication $application, string $type): BinaryFileResponse|RedirectResponse
    {
        abort_unless($application->program?->type === 'internship', 404);

        $path = $application->documentFiles()[$type]['path'] ?? null;

        abort_unless(filled($path), 404);

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return redirect()->away($path);
        }

        $absolute = resolve_public_upload($path);
        abort_if($absolute === null, 404, 'Berkas tidak ditemukan di server.');

        $extension = strtolower(pathinfo($absolute, PATHINFO_EXTENSION) ?: 'pdf');
        $filename = $application->documentFilename($type, $extension);
        $forceDownload = $request->boolean('download');
        $inline = ! $forceDownload && in_array($extension, ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'gif'], true);

        return response()->file($absolute, [
            'Content-Type' => match ($extension) {
                'pdf' => 'application/pdf',
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'webp' => 'image/webp',
                'gif' => 'image/gif',
                default => 'application/octet-stream',
            },
            'Content-Disposition' => ($inline ? 'inline' : 'attachment').'; filename="'.$filename.'"',
        ]);
    }

    /**
     * @return array{program_id: int, division: string, search: string, status: string}
     */
    private function filters(): array
    {
        return [
            'program_id' => request()->integer('program'),
            'division' => trim(request()->string('division')->toString()),
            'search' => trim(request()->string('q')->toString()),
            'status' => trim(request()->string('status')->toString()),
        ];
    }

    /**
     * @param  array{program_id: int, division: string, search: string, status: string}  $filters
     */
    private function filteredQuery(array $filters): Builder
    {
        $query = InternshipApplication::with(['user', 'program'])
            ->whereHas('program', function ($q) use ($filters) {
                $q->where('type', 'internship');
                if ($filters['division'] !== '') {
                    $q->where('division', $filters['division']);
                }
            })
            ->latest();

        if ($filters['program_id'] > 0) {
            $query->where('program_id', $filters['program_id']);
        }

        if ($filters['status'] === 'pending') {
            $query->whereIn('status', ['submitted', 'under_review']);
        } elseif (in_array($filters['status'], ['accepted', 'rejected', 'under_review'], true)) {
            $query->where('status', $filters['status']);
        }

        if ($filters['search'] !== '') {
            $needle = '%'.mb_strtolower($filters['search']).'%';
            $query->where(function ($q) use ($needle) {
                $q->whereRaw('LOWER(full_name) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(COALESCE(university, \'\')) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(COALESCE(major, \'\')) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(COALESCE(phone, \'\')) LIKE ?', [$needle])
                    ->orWhereHas('user', fn ($u) => $u->whereRaw('LOWER(email) LIKE ?', [$needle]))
                    ->orWhereHas('program', fn ($p) => $p->whereRaw('LOWER(title) LIKE ?', [$needle]));
            });
        }

        return $query;
    }

    /**
     * @param  array{program_id: int, division: string, search: string, status: string}  $filters
     */
    private function baseQuery(array $filters): Builder
    {
        $query = InternshipApplication::query()
            ->whereHas('program', function ($q) use ($filters) {
                $q->where('type', 'internship');
                if ($filters['division'] !== '') {
                    $q->where('division', $filters['division']);
                }
            });

        if ($filters['program_id'] > 0) {
            $query->where('program_id', $filters['program_id']);
        }

        return $query;
    }
}
