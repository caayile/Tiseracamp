<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    public function index(): View
    {
        $testimonials = Testimonial::query()
            ->with(['user:id,name', 'program:id,title,type'])
            ->latest()
            ->paginate(20);

        return view('admin.testimonials.index', compact('testimonials'));
    }

    public function publish(Testimonial $testimonial): RedirectResponse
    {
        $testimonial->update(['is_published' => ! $testimonial->is_published]);
        forget_home_cache();

        if ($testimonial->is_published) {
            notify_user(
                $testimonial->user_id,
                'Testimoni tayang',
                'Testimonimu sudah tampil di beranda. Terima kasih!',
                'success',
                route('home').'#testimoni'
            );
        }

        return back()->with('success', $testimonial->is_published ? 'Testimoni dipublikasikan.' : 'Testimoni disembunyikan.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $testimonial->delete();
        forget_home_cache();

        return back()->with('success', 'Testimoni dihapus.');
    }
}
