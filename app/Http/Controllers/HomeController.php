<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Faq;
use App\Models\Partner;
use App\Models\Program;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
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
        $partners = Partner::latest()->take(8)->get();
        $banners = Banner::where('is_active', true)->latest()->take(3)->get();
        $faqs = Faq::where('is_published', true)->orderBy('sort_order')->take(6)->get();
        $articles = Article::where('is_published', true)->latest()->take(3)->get();

        return view('home', compact('featured', 'programs', 'categories', 'partners', 'banners', 'faqs', 'articles'));
    }
}
