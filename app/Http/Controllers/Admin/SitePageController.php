<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SitePage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SitePageController extends Controller
{
    public function edit(): View
    {
        $pages = collect(['terms', 'privacy'])->map(fn (string $slug) => SitePage::bySlug(
            $slug,
            $slug === 'terms' ? 'Syarat & Ketentuan' : 'Kebijakan Privasi',
            $slug === 'terms' ? SitePage::defaultTerms() : SitePage::defaultPrivacy()
        ));

        return view('admin.site-pages.edit', compact('pages'));
    }

    public function update(Request $request, SitePage $sitePage): RedirectResponse
    {
        abort_unless(in_array($sitePage->slug, ['terms', 'privacy'], true), 404);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'body' => ['required', 'string'],
        ]);

        $sitePage->update($data);

        return back()->with('success', $sitePage->title.' diperbarui.');
    }
}
