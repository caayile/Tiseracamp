<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Enrollment;
use App\Models\InternshipApplication;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function index(): View
    {
        $programId = request()->integer('program');
        $division = trim(request()->string('division')->toString());
        $search = trim(request()->string('q')->toString());
        $status = trim(request()->string('status')->toString());

        $baseQuery = InternshipApplication::query()
            ->whereHas('program', fn ($q) => $q->where('type', 'internship'));

        $query = InternshipApplication::with(['user', 'program'])
            ->whereHas('program', function ($q) use ($division) {
                $q->where('type', 'internship');
                if ($division !== '') {
                    $q->where('division', $division);
                }
            })
            ->latest();

        $filterProgram = null;
        if ($programId > 0) {
            $query->where('program_id', $programId);
            $baseQuery->where('program_id', $programId);
            $filterProgram = Program::query()->find($programId);
        }

        if ($status === 'pending') {
            $query->whereIn('status', ['submitted', 'under_review']);
        } elseif (in_array($status, ['accepted', 'rejected', 'under_review'], true)) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $needle = '%'.mb_strtolower($search).'%';
            $query->where(function ($q) use ($needle) {
                $q->whereRaw('LOWER(full_name) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(COALESCE(university, \'\')) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(COALESCE(major, \'\')) LIKE ?', [$needle])
                    ->orWhereHas('user', fn ($u) => $u->whereRaw('LOWER(email) LIKE ?', [$needle]))
                    ->orWhereHas('program', fn ($p) => $p->whereRaw('LOWER(title) LIKE ?', [$needle]));
            });
        }

        $applications = $query->paginate(20)->withQueryString();

        $rawCounts = (clone $baseQuery)
            ->when($division !== '', fn ($q) => $q->whereHas('program', fn ($p) => $p->where('division', $division)))
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

        return view('admin.applications.index', compact(
            'applications',
            'filterProgram',
            'divisions',
            'division',
            'search',
            'status',
            'statusCounts',
        ));
    }

    public function review(Request $request, InternshipApplication $application): RedirectResponse
    {
        abort_unless($application->program?->type === 'internship', 404);

        $data = $request->validate([
            'status' => ['required', 'in:accepted,rejected,under_review'],
            'reviewer_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $application->update([
            'status' => $data['status'],
            'reviewer_note' => $data['reviewer_note'] ?? null,
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
}
