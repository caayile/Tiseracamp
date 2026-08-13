<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AchievementController extends Controller
{
    public function index(): View
    {
        $achievements = Achievement::query()->withCount('users')->latest()->get();
        $editing = request()->filled('edit')
            ? Achievement::find(request('edit'))
            : null;

        return view('admin.achievements.index', compact('achievements', 'editing'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['code'] = $data['code'] ?: Str::slug($data['name'], '_');

        Achievement::create($data);

        return redirect()->route('admin.achievements.index')->with('success', 'Badge ditambahkan.');
    }

    public function update(Request $request, Achievement $achievement): RedirectResponse
    {
        $data = $this->validated($request, $achievement->id);
        $data['code'] = $data['code'] ?: ($achievement->code ?: Str::slug($data['name'], '_'));
        $achievement->update($data);

        return redirect()->route('admin.achievements.index')->with('success', 'Badge diperbarui.');
    }

    public function destroy(Achievement $achievement): RedirectResponse
    {
        $achievement->delete();

        return back()->with('success', 'Badge dihapus.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $request->merge([
            'code' => filled($request->input('code')) ? Str::slug((string) $request->input('code'), '_') : null,
        ]);

        return $request->validate([
            'code' => ['nullable', 'string', 'max:64', 'unique:achievements,code,'.($ignoreId ?: 'NULL').',id'],
            'name' => ['required', 'string', 'max:120'],
            'icon' => ['nullable', 'string', 'max:16'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
