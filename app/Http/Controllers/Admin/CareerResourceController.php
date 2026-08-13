<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CareerResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CareerResourceController extends Controller
{
    public function index(): View
    {
        $resources = CareerResource::query()->latest()->get();
        $editing = request()->filled('edit')
            ? CareerResource::find(request('edit'))
            : null;

        return view('admin.career-resources.index', compact('resources', 'editing'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['is_published'] = $request->boolean('is_published');

        CareerResource::create($data);

        return redirect()->route('admin.career-resources.index')->with('success', 'Materi karier ditambahkan.');
    }

    public function update(Request $request, CareerResource $careerResource): RedirectResponse
    {
        $data = $this->validated($request);
        $data['is_published'] = $request->boolean('is_published');
        $careerResource->update($data);

        return redirect()->route('admin.career-resources.index')->with('success', 'Materi karier diperbarui.');
    }

    public function destroy(CareerResource $careerResource): RedirectResponse
    {
        $careerResource->delete();

        return back()->with('success', 'Materi karier dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'type' => ['required', 'in:cv,interview,job'],
            'content' => ['required', 'string'],
            'file_url' => ['nullable', 'url', 'max:500'],
        ]);
    }
}
