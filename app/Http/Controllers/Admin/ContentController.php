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
            'articles' => Article::latest()->take(10)->get(),
            'banners' => Banner::latest()->get(),
            'faqs' => Faq::orderBy('sort_order')->get(),
            'categories' => Category::withCount('programs')->get(),
            'announcements' => Announcement::with('user')->latest()->take(10)->get(),
        ]);
    }

    public function storeArticle(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string'],
            'excerpt' => ['nullable', 'string'],
            'body' => ['nullable', 'string'],
        ]);
        Article::create([
            ...$data,
            'slug' => Str::slug($data['title']).'-'.Str::random(4),
            'is_published' => true,
        ]);

        return back()->with('success', 'Artikel ditambahkan.');
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
