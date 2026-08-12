<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->string('type')->toString();
        $search = trim($request->string('q')->toString());

        $query = Portfolio::with('user')->latest();
        if (in_array($type, ['portfolio', 'cv'], true)) {
            $query->where('type', $type);
        }
        if ($search !== '') {
            $needle = '%'.mb_strtolower($search).'%';
            $query->where(function ($subQuery) use ($needle) {
                $subQuery->whereRaw('LOWER(title) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(COALESCE(description, \'\')) LIKE ?', [$needle])
                    ->orWhereHas('user', fn ($q) => $q->whereRaw('LOWER(name) LIKE ?', [$needle]));
            });
        }

        $portfolios = $query->paginate(12)->withQueryString();

        $users = User::orderBy('name')->get();

        return view('admin.portfolios.index', compact('portfolios', 'users', 'type', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'type' => ['required', 'in:portfolio,cv'],
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string'],
            'project_url' => ['nullable', 'url'],
            'portfolio_file' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        if ($request->hasFile('portfolio_file')) {
            $data['portfolio_file_url'] = $request->file('portfolio_file')->store(
                $data['type'] === 'cv' ? 'cvs' : 'portfolios',
                media_disk()
            );
        }

        Portfolio::create([
            'user_id' => $data['user_id'],
            'type' => $data['type'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'project_url' => $data['project_url'] ?? null,
            'portfolio_file_url' => $data['portfolio_file_url'] ?? null,
        ]);

        $label = $data['type'] === 'cv' ? 'CV' : 'Portofolio';

        return redirect()->route('admin.portfolios.index')
            ->with('success', $label.' berhasil ditambahkan.');
    }

    public function destroy(Portfolio $portfolio): RedirectResponse
    {
        $portfolio->delete();

        return back()->with('success', 'Portofolio dihapus.');
    }
}
