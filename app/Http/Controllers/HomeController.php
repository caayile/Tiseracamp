<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Faq;
use App\Models\Partner;
use App\Models\Portfolio;
use App\Models\Program;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $data = Cache::remember('home.page.v3', 90, function () {
            $featured = Program::published()
                ->with(['partner', 'mentor'])
                ->where('type', 'bootcamp')
                ->where('is_featured', true)
                ->latest()
                ->take(3)
                ->get();

            $programs = Program::published()
                ->with(['partner', 'mentor'])
                ->where('type', 'bootcamp')
                ->latest()
                ->take(6)
                ->get();

            $categories = Category::query()->orderBy('name')->get();
            $partners = Partner::query()
                ->whereNotNull('logo')
                ->orderBy('name')
                ->get();
            $banners = Banner::where('is_active', true)->latest()->take(3)->get();
            $faqs = Faq::where('is_published', true)->orderBy('sort_order')->take(6)->get();
            $articles = Article::where('is_published', true)->latest('published_at')->latest('id')->take(3)->get();
            $testimonials = Testimonial::query()
                ->with(['user:id,name,university,avatar', 'program:id,title,type'])
                ->where('is_published', true)
                ->latest()
                ->take(12)
                ->get();

            // Tanpa whereHas berantai (mahal di Neon). Fallback tetap portofolio terbaru.
            $portfolios = Portfolio::query()
                ->with(['user:id,name,university,avatar'])
                ->where('type', 'portfolio')
                ->latest()
                ->take(12)
                ->get();

            $heroStudents = User::query()
                ->where('role', 'student')
                ->where('status', 'active')
                ->latest('id')
                ->take(5)
                ->get(['id', 'name', 'avatar'])
                ->map(fn (User $user) => (object) [
                    'name' => $user->name,
                    'avatar' => $user->avatar,
                ]);

            $studentCount = User::query()
                ->where('role', 'student')
                ->where('status', 'active')
                ->count();

            return compact(
                'featured',
                'programs',
                'categories',
                'partners',
                'banners',
                'faqs',
                'articles',
                'testimonials',
                'portfolios',
                'heroStudents',
                'studentCount'
            );
        });

        return view('home', $data);
    }
}
