<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CvPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CvPlanController extends Controller
{
    public function index(): View
    {
        $plans = CvPlan::orderBy('sort_order')->orderBy('id')->get();

        return view('admin.cv-plans.index', compact('plans'));
    }

    public function create(): View
    {
        return view('admin.cv-plans.form', ['plan' => new CvPlan([
            'sort_order' => (int) CvPlan::max('sort_order') + 1,
            'is_active' => true,
        ])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $plan = CvPlan::create($data);
        ActivityLog::record(auth()->user(), 'create_cv_plan', $plan, $plan->name);

        return redirect()->route('admin.cv-plans.index')->with('success', 'Paket CV '.$plan->name.' dibuat.');
    }

    public function edit(CvPlan $cvPlan): View
    {
        return view('admin.cv-plans.form', ['plan' => $cvPlan]);
    }

    public function update(Request $request, CvPlan $cvPlan): RedirectResponse
    {
        $data = $this->validated($request, $cvPlan);

        $cvPlan->update($data);
        ActivityLog::record(auth()->user(), 'update_cv_plan', $cvPlan, $cvPlan->name);

        return redirect()->route('admin.cv-plans.index')->with('success', 'Paket CV '.$cvPlan->name.' diperbarui.');
    }

    public function destroy(CvPlan $cvPlan): RedirectResponse
    {
        $cvPlan->delete();
        ActivityLog::record(auth()->user(), 'delete_cv_plan', null, $cvPlan->name);

        return back()->with('success', 'Paket CV '.$cvPlan->name.' dihapus.');
    }

    private function validated(Request $request, ?CvPlan $cvPlan = null): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:40', 'regex:/^[a-z0-9_-]+$/'],
            'name' => ['required', 'string', 'max:120'],
            'tagline' => ['nullable', 'string', 'max:160'],
            'price' => ['required', 'integer', 'min:0'],
            'reviews' => ['nullable', 'integer', 'min:0'],
            'days' => ['required', 'integer', 'min:1'],
            'badge' => ['nullable', 'string', 'max:60'],
            'features_text' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $data['features'] = $this->parseLines($request->input('features_text'));
        $data['is_active'] = (bool) ($request->boolean('is_active'));
        $data['reviews'] = $request->filled('reviews') ? (int) $data['reviews'] : null;

        unset($data['features_text']);

        return $data;
    }

    private function parseLines(?string $text): array
    {
        if (! $text) {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n/', $text))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }
}
