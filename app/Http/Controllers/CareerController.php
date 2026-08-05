<?php

namespace App\Http\Controllers;

use App\Models\CareerResource;
use App\Models\Certificate;
use App\Models\Portfolio;
use App\Models\Program;
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

    public function gallery(Request $request): View
    {
        $search = trim($request->string('q')->toString());

        $portfolios = Portfolio::with('user')
            ->when($search, fn ($query) => $query->where(function ($subQuery) use ($search) {
                $needle = '%'.mb_strtolower($search).'%';
                $subQuery->whereRaw('LOWER(title) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(COALESCE(description, \'\')) LIKE ?', [$needle])
                    ->orWhereHas('user', fn ($q) => $q->whereRaw('LOWER(name) LIKE ?', [$needle]));
            }))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('career.gallery', compact('portfolios', 'search'));
    }

    public function jobs(Request $request): View
    {
        $search = trim($request->string('q')->toString());

        $programs = Program::published()
            ->with(['partner', 'mentor', 'category'])
            ->where('type', 'internship')
            ->when($search, fn ($query) => $query->where(function ($subQuery) use ($search) {
                $needle = '%'.mb_strtolower($search).'%';
                $subQuery->whereRaw('LOWER(title) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(COALESCE(excerpt, \'\')) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(COALESCE(division, \'\')) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(COALESCE(location, \'\')) LIKE ?', [$needle]);
            }))
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('career.jobs', compact('programs', 'search'));
    }

    public function storePortfolio(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string'],
            'project_url' => ['nullable', 'url'],
            'portfolio_file' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        if ($request->hasFile('portfolio_file')) {
            $data['portfolio_file_url'] = $request->file('portfolio_file')->store('portfolios', media_disk());
        }

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
