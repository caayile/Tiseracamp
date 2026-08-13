<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    public function create(Enrollment $enrollment): View|RedirectResponse
    {
        abort_unless($enrollment->user_id === auth()->id(), 403);

        if (! $enrollment->isCompleted()) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Testimoni hanya tersedia setelah magang/bootcamp selesai.');
        }

        $enrollment->load(['program', 'testimonial']);

        if ($enrollment->testimonial) {
            return redirect()
                ->route('dashboard')
                ->with('success', $enrollment->testimonial->is_published
                    ? 'Testimoni untuk program ini sudah tayang di beranda.'
                    : 'Testimoni sudah terkirim dan menunggu review admin.');
        }

        return view('testimonials.create', compact('enrollment'));
    }

    public function store(Request $request, Enrollment $enrollment): RedirectResponse
    {
        abort_unless($enrollment->user_id === auth()->id(), 403);

        if (! $enrollment->isCompleted()) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Testimoni hanya tersedia setelah magang/bootcamp selesai.');
        }

        if ($enrollment->testimonial()->exists()) {
            return redirect()
                ->route('dashboard')
                ->with('success', 'Testimoni untuk program ini sudah terkirim.');
        }

        $data = $request->validate([
            'body' => ['required', 'string', 'min:30', 'max:600'],
            'role_label' => ['nullable', 'string', 'max:120'],
        ]);

        Testimonial::create([
            'user_id' => auth()->id(),
            'enrollment_id' => $enrollment->id,
            'program_id' => $enrollment->program_id,
            'body' => trim($data['body']),
            'role_label' => filled($data['role_label'] ?? null)
                ? trim($data['role_label'])
                : ($enrollment->program?->title ?: $enrollment->program?->typeLabel()),
            'is_published' => false,
        ]);

        notify_admins(
            'Testimoni baru',
            auth()->user()->name.' mengirim testimoni untuk '.$enrollment->program?->title.'.',
            'info',
            route('admin.testimonials.index')
        );

        return redirect()
            ->route('dashboard')
            ->with('success', 'Terima kasih! Testimoni menunggu review admin sebelum tampil di beranda.');
    }
}
