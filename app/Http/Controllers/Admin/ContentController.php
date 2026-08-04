<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AppNotification;
use App\Models\Article;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Faq;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ContentController extends Controller
{
    public function index(): View
    {
        return view('admin.content.index', [
            'articles' => Article::query()->latest('published_at')->latest('id')->take(20)->get(),
            'editingArticle' => request()->filled('edit')
                ? Article::find(request('edit'))
                : null,
            'banners' => Banner::latest()->get(),
            'faqs' => Faq::orderBy('sort_order')->get(),
            'categories' => Category::withCount('programs')->get(),
            'announcements' => Announcement::with('user')->latest()->take(10)->get(),
        ]);
    }

    public function storeArticle(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'published_at' => ['required', 'date'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('articles', media_disk());
        }

        Article::create([
            'title' => $data['title'],
            'excerpt' => $data['excerpt'] ?? null,
            'body' => $data['body'],
            'thumbnail' => $thumbnailPath,
            'slug' => Str::slug($data['title']).'-'.Str::random(4),
            'is_published' => $request->boolean('is_published'),
            'published_at' => $data['published_at'],
        ]);

        return redirect()->route('admin.content.index')->with('success', 'Berita dipublikasikan.');
    }

    public function updateArticle(Request $request, Article $article): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'published_at' => ['required', 'date'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('thumbnail')) {
            $article->thumbnail = $request->file('thumbnail')->store('articles', media_disk());
        }

        $article->fill([
            'title' => $data['title'],
            'excerpt' => $data['excerpt'] ?? null,
            'body' => $data['body'],
            'is_published' => $request->boolean('is_published'),
            'published_at' => $data['published_at'],
        ])->save();

        return redirect()->route('admin.content.index')->with('success', 'Berita diperbarui.');
    }

    public function destroyArticle(Article $article): RedirectResponse
    {
        $article->delete();

        return back()->with('success', 'Berita dihapus.');
    }

    public function storeBanner(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string'],
            'subtitle' => ['nullable', 'string'],
            'cta_text' => ['nullable', 'string'],
            'cta_link' => ['nullable', 'string'],
        ]);
        Banner::create([...$data, 'is_active' => true]);

        return back()->with('success', 'Banner ditambahkan.');
    }

    public function storeFaq(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'question' => ['required', 'string'],
            'answer' => ['required', 'string'],
        ]);
        Faq::create([...$data, 'sort_order' => Faq::count() + 1, 'is_published' => true]);

        return back()->with('success', 'FAQ ditambahkan.');
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:80']]);
        Category::create(['name' => $data['name'], 'slug' => Str::slug($data['name'])]);

        return back()->with('success', 'Kategori ditambahkan.');
    }

    public function broadcast(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string'],
            'body' => ['required', 'string'],
            'audience' => ['required', 'in:all,student,mentor'],
        ]);

        Announcement::create([
            'user_id' => auth()->id(),
            'title' => $data['title'],
            'body' => $data['body'],
            'is_global' => true,
        ]);

        $users = User::query()
            ->when($data['audience'] !== 'all', fn ($q) => $q->where('role', $data['audience']))
            ->whereIn('role', $data['audience'] === 'all' ? ['student', 'mentor'] : [$data['audience']])
            ->get();

        foreach ($users as $user) {
            AppNotification::create([
                'user_id' => $user->id,
                'title' => $data['title'],
                'body' => $data['body'],
                'type' => 'broadcast',
            ]);
        }

        return back()->with('success', 'Broadcast dikirim ke '.$users->count().' user.');
    }
}
