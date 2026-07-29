<?php

namespace App\Http\Controllers;

use App\Models\CareerResource;
use App\Models\Certificate;
use App\Models\Portfolio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CareerController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $certificates = Certificate::whereHas('enrollment', fn ($q) => $q->where('user_id', $user->id))
            ->with('enrollment.program')->latest()->get();
        $achievements = $user->achievements;
        $portfolios = $user->portfolios()->latest()->get();
        $resources = CareerResource::where('is_published', true)->latest()->get();

        return view('career.index', compact('certificates', 'achievements', 'portfolios', 'resources'));
    }

    public function storePortfolio(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string'],
            'project_url' => ['nullable', 'url'],
        ]);

        auth()->user()->portfolios()->create($data);

        return back()->with('success', 'Portfolio ditambahkan.');
    }

    public function destroyPortfolio(Portfolio $portfolio): RedirectResponse
    {
        abort_unless($portfolio->user_id === auth()->id(), 403);
        $portfolio->delete();

        return back()->with('success', 'Portfolio dihapus.');
    }
}
