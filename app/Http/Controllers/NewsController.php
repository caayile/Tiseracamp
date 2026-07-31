<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $articles = Article::query()
            ->where('is_published', true)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('title', 'ilike', "%{$q}%")
                        ->orWhere('excerpt', 'ilike', "%{$q}%")
                        ->orWhere('body', 'ilike', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('news.index', compact('articles', 'q'));
    }

    public function show(string $slug): View
    {
        $article = Article::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        return view('news.show', compact('article'));
    }
}
