<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CareerController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('career.gallery');
    }

    public function gallery(Request $request): View
    {
        $search = trim($request->string('q')->toString());

        $portfolios = Portfolio::with('user')
            ->where('type', 'portfolio')
            ->when($search, fn ($query) => $query->where(function ($subQuery) use ($search) {
                $needle = '%'.mb_strtolower($search).'%';
                $subQuery->whereRaw('LOWER(title) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(COALESCE(description, \'\')) LIKE ?', [$needle])
                    ->orWhereHas('user', fn ($q) => $q->whereRaw('LOWER(name) LIKE ?', [$needle]));
            }))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $myPortfolios = auth()->user()->portfolios()
            ->where('type', 'portfolio')
            ->latest()
            ->get();
        $myCvs = auth()->user()->portfolios()
            ->where('type', 'cv')
            ->latest()
            ->get();

        return view('career.gallery', compact('portfolios', 'search', 'myPortfolios', 'myCvs'));
    }

    public function jobs(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $isTsuStudent = auth()->user()?->isTsuStudent() ?? false;
        $scope = $isTsuStudent && $request->string('scope')->toString() === 'tsu' ? 'tsu' : 'all';

        $programs = Program::published()
            ->with(['partner', 'mentor', 'category'])
            ->where('type', 'job')
            ->forAudience($scope === 'tsu')
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

        return view('career.jobs', compact('programs', 'search', 'isTsuStudent', 'scope'));
    }

    public function storePortfolio(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:portfolio,cv'],
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string'],
            'project_url' => ['nullable', 'url'],
            'portfolio_file' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        if ($data['type'] === 'cv' && ! $request->hasFile('portfolio_file') && blank($data['project_url'] ?? null)) {
            return back()
                ->withInput()
                ->withErrors(['portfolio_file' => 'Untuk CV, upload file PDF atau isi link.']);
        }

        if ($request->hasFile('portfolio_file')) {
            $data['portfolio_file_url'] = $request->file('portfolio_file')->store(
                $data['type'] === 'cv' ? 'cvs' : 'portfolios',
                media_disk()
            );
        }

        auth()->user()->portfolios()->create([
            'type' => $data['type'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'project_url' => $data['project_url'] ?? null,
            'portfolio_file_url' => $data['portfolio_file_url'] ?? null,
        ]);

        $label = $data['type'] === 'cv' ? 'CV' : 'Portofolio';

        return redirect()
            ->route('career.gallery')
            ->with('success', $label.' berhasil disimpan. Nanti otomatis terisi saat daftar magang.');
    }

    public function destroyPortfolio(Portfolio $portfolio): RedirectResponse
    {
        abort_unless($portfolio->user_id === auth()->id(), 403);
        $portfolio->delete();

        return back()->with('success', 'Portfolio dihapus.');
    }
}
