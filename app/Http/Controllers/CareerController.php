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
            ->orderOpenFirst()
            ->paginate(9)
            ->withQueryString();

        return view('career.jobs', compact('programs', 'search', 'isTsuStudent', 'scope'));
    }

    public function storePortfolio(Request $request): RedirectResponse
    {
        // Normalisasi URL jika user mengetik domain tanpa protokol
        if ($request->filled('project_url')) {
            $rawUrl = trim((string) $request->input('project_url'));
            if (! preg_match('~^https?://~i', $rawUrl)) {
                $request->merge(['project_url' => 'https://' . $rawUrl]);
            }
        }

        $data = $request->validate([
            'type'           => ['required', 'in:portfolio,cv'],
            'title'          => ['required', 'string', 'max:160'],
            'description'    => ['nullable', 'string', 'max:2000'],
            'project_url'    => ['nullable', 'url', 'max:255'],
            'portfolio_file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            // Gambar maks 10 MB (10240 KB)
            'project_image'  => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif,svg,bmp,avif,jfif,heic', 'max:10240'],
        ], [
            'project_image.mimes'    => 'Format gambar harus JPG, PNG, WebP, GIF, SVG, atau BMP.',
            'project_image.max'      => 'Ukuran gambar maksimal 10 MB.',
            'portfolio_file.mimes'   => 'File harus berformat PDF.',
            'portfolio_file.max'     => 'Ukuran file PDF maksimal 10 MB.',
            'project_url.url'        => 'Link proyek tidak valid (contoh: https://github.com/...).',
            'title.required'         => 'Judul wajib diisi.',
            'title.max'              => 'Judul maksimal 160 karakter.',
        ]);

        // Untuk CV: harus ada file PDF atau link
        if ($data['type'] === 'cv' && ! $request->hasFile('portfolio_file') && blank($data['project_url'] ?? null)) {
            return back()
                ->withInput()
                ->withErrors(['portfolio_file' => 'Untuk CV, unggah file PDF atau isi link.']);
        }

        // Untuk Portofolio: gambar proyek WAJIB
        if ($data['type'] === 'portfolio' && ! $request->hasFile('project_image')) {
            return back()
                ->withInput()
                ->withErrors(['project_image' => 'Gambar proyek wajib diunggah untuk portofolio.']);
        }

        // Simpan file PDF jika ada
        $pdfPath = null;
        if ($request->hasFile('portfolio_file')) {
            $pdfPath = $request->file('portfolio_file')->store(
                $data['type'] === 'cv' ? 'cvs' : 'portfolios',
                media_disk()
            );
        }

        // Simpan gambar proyek jika ada
        $imagePath = null;
        if ($request->hasFile('project_image')) {
            $imagePath = $request->file('project_image')->store('portfolio-images', media_disk());
        }

        auth()->user()->portfolios()->create([
            'type'               => $data['type'],
            'title'              => $data['title'],
            'description'        => $data['description'] ?? null,
            'project_url'        => $data['project_url'] ?? null,
            'portfolio_file_url' => $pdfPath,
            'image_path'         => $imagePath,
        ]);

        award_achievement(auth()->user(), 'first_portfolio');
        forget_home_cache();

        $label = $data['type'] === 'cv' ? 'CV' : 'Portofolio';

        return redirect()
            ->route('career.gallery')
            ->with('success', $label.' berhasil disimpan. Nanti otomatis terisi saat daftar magang.');
    }

    public function destroyPortfolio(Portfolio $portfolio): RedirectResponse
    {
        abort_unless($portfolio->user_id === auth()->id(), 403);
        $portfolio->delete();
        forget_home_cache();

        return back()->with('success', 'Portofolio berhasil dihapus.');
    }
}
