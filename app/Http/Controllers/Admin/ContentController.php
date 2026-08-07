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

        forget_home_cache();

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

        forget_home_cache();

        return redirect()->route('admin.content.index')->with('success', 'Berita diperbarui.');
    }

    public function destroyArticle(Article $article): RedirectResponse
    {
        $article->delete();

        forget_home_cache();

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

        forget_home_cache();

        return back()->with('success', 'Banner ditambahkan.');
    }

    public function updateBanner(Request $request, Banner $banner): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string'],
            'subtitle' => ['nullable', 'string'],
            'cta_text' => ['nullable', 'string'],
            'cta_link' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $banner->update([
            ...$data,
            'is_active' => $request->boolean('is_active'),
        ]);

        forget_home_cache();

        return back()->with('success', 'Banner diperbarui.');
    }

    public function destroyBanner(Banner $banner): RedirectResponse
    {
        $banner->delete();

        forget_home_cache();

        return back()->with('success', 'Banner dihapus.');
    }

    public function storeFaq(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'question' => ['required', 'string'],
            'answer' => ['required', 'string'],
        ]);
        Faq::create([...$data, 'sort_order' => Faq::count() + 1, 'is_published' => true]);

        forget_home_cache();

        return back()->with('success', 'FAQ ditambahkan.');
    }

    public function updateFaq(Request $request, Faq $faq): RedirectResponse
    {
        $data = $request->validate([
            'question' => ['required', 'string'],
            'answer' => ['required', 'string'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $faq->update([
            'question' => $data['question'],
            'answer' => $data['answer'],
            'is_published' => $request->boolean('is_published'),
        ]);

        forget_home_cache();

        return back()->with('success', 'FAQ diperbarui.');
    }

    public function destroyFaq(Faq $faq): RedirectResponse
    {
        $faq->delete();

        forget_home_cache();

        return back()->with('success', 'FAQ dihapus.');
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:80']]);
        Category::create(['name' => $data['name'], 'slug' => Str::slug($data['name'])]);

        forget_home_cache();

        return back()->with('success', 'Kategori ditambahkan.');
    }

    public function updateCategory(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:80']]);
        $category->update([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
        ]);

        forget_home_cache();

        return back()->with('success', 'Kategori diperbarui.');
    }

    public function destroyCategory(Category $category): RedirectResponse
    {
        if ($category->programs()->exists()) {
            return back()->with('error', 'Kategori masih dipakai program. Pindahkan dulu.');
        }

        $category->delete();

        forget_home_cache();

        return back()->with('success', 'Kategori dihapus.');
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
