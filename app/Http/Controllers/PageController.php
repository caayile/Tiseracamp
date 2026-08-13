<?php

namespace App\Http\Controllers;

use App\Models\SitePage;
use Illuminate\View\View;

class PageController extends Controller
{
    public function terms(): View
    {
        $page = SitePage::bySlug('terms', 'Syarat & Ketentuan', SitePage::defaultTerms());

        return view('pages.legal', compact('page'));
    }

    public function privacy(): View
    {
        $page = SitePage::bySlug('privacy', 'Kebijakan Privasi', SitePage::defaultPrivacy());

        return view('pages.legal', compact('page'));
    }
}
