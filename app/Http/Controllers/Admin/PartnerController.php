<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PartnerController extends Controller
{
    public function index(): View
    {
        $partners = Partner::query()->withCount('programs')->orderBy('name')->get();
        $editing = request()->filled('edit') ? Partner::find(request('edit')) : null;

        return view('admin.partners.index', compact('partners', 'editing'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'website' => ['nullable', 'url', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('partners', media_disk());
        }

        Partner::create($data);
        forget_home_cache();

        return redirect()->route('admin.partners.index')->with('success', 'Mitra ditambahkan.');
    }

    public function update(Request $request, Partner $partner): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'website' => ['nullable', 'url', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('partners', media_disk());
        } else {
            unset($data['logo']);
        }

        $partner->update($data);
        forget_home_cache();

        return redirect()->route('admin.partners.index')->with('success', 'Mitra diperbarui.');
    }

    public function destroy(Partner $partner): RedirectResponse
    {
        if ($partner->programs()->exists()) {
            return back()->with('error', 'Mitra masih dipakai program. Pindahkan dulu.');
        }

        $partner->delete();
        forget_home_cache();

        return back()->with('success', 'Mitra dihapus.');
    }
}
