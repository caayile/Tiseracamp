<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\InternshipApplication;
use App\Models\LogbookEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $user = auth()->user();

        return view('profile.edit', compact('user'));
    }

    public function applications(): View
    {
        $user = auth()->user();
        $applications = InternshipApplication::with('program')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $enrollments = Enrollment::with('program')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return view('profile.applications', compact('user', 'applications', 'enrollments'));
    }

    public function logbook(): View
    {
        $user = auth()->user();
        $enrollments = Enrollment::with('program')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $internshipEnrollments = $enrollments->filter(fn ($e) => $e->program?->type === 'internship');

        $logbooks = LogbookEntry::with('program')
            ->where('user_id', $user->id)
            ->latest('entry_date')
            ->get();

        return view('profile.logbook', compact('user', 'internshipEnrollments', 'logbooks'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'university' => ['nullable', 'string', 'max:160'],
            'major' => ['nullable', 'string', 'max:120'],
            'semester' => ['nullable', 'string', 'max:40'],
            'education_level' => ['nullable', 'in:D3,D4,S1'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'expertise' => ['nullable', 'string', 'max:500'],
            'password' => ['nullable', 'confirmed', 'min:8'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        $user->name = $data['name'];
        $user->phone = $data['phone'] ?? null;
        $user->university = $data['university'] ?? null;
        $user->major = $data['major'] ?? null;
        $user->semester = $data['semester'] ?? null;
        $user->education_level = $data['education_level'] ?? null;
        $user->bio = $data['bio'] ?? null;

        if ($user->isMentor()) {
            $user->expertise = array_values(array_filter(array_map('trim', explode(',', $data['expertise'] ?? ''))));
        }

        if ($request->hasFile('avatar')) {
            $user->avatar = $request->file('avatar')->store('avatars', media_disk());
        }

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Profil diperbarui.');
    }
}
