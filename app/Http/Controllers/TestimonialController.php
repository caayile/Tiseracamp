<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    public function create(Enrollment $enrollment): View
    {
        abort_unless($enrollment->user_id === auth()->id(), 403);
        abort_unless($enrollment->isCompleted(), 403, 'Testimoni hanya tersedia setelah magang/bootcamp selesai.');
        abort_unless(! $enrollment->testimonial, 403, 'Kamu sudah mengirim testimoni untuk program ini.');

        $enrollment->load('program');

        return view('testimonials.create', compact('enrollment'));
    }

    public function store(Request $request, Enrollment $enrollment): RedirectResponse
    {
        abort_unless($enrollment->user_id === auth()->id(), 403);
        abort_unless($enrollment->isCompleted(), 403, 'Testimoni hanya tersedia setelah magang/bootcamp selesai.');
        abort_if($enrollment->testimonial()->exists(), 422, 'Testimoni sudah ada.');

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
            'is_published' => true,
        ]);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Terima kasih! Testimoni magang/bootcamp-mu sudah tampil di beranda.');
    }
}
