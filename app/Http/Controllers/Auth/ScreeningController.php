<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScreeningController extends Controller
{
    public function show(): View|RedirectResponse
    {
        $user = auth()->user();

        if (! $user->isStudent()) {
            return redirect()->route($user->dashboardRoute());
        }

        if ($user->hasCompletedScreening()) {
            return redirect()->route('dashboard');
        }

        return view('auth.screening');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isStudent()) {
            return redirect()->route($user->dashboardRoute());
        }

        $data = $request->validate([
            'is_tsu' => ['required', 'in:0,1'],
            'ktm' => [
                'nullable',
                'required_if:is_tsu,1',
                'file',
                'mimes:png,jpg,jpeg,pdf',
                'max:5120',
            ],
        ]);

        $ktmPath = null;
        if ($data['is_tsu'] === '1' && $request->hasFile('ktm')) {
            $ktmPath = $request->file('ktm')->store('ktm', media_disk());
        }

        $user->forceFill([
            'is_tsu' => (bool) $data['is_tsu'],
            'ktm_path' => $ktmPath,
            'screening_completed_at' => $user->screening_completed_at ?? now(),
        ])->save();

        return redirect()
            ->route('dashboard')
            ->with('success', $user->isTsuStudent()
                ? 'Terima kasih! Kartu Tanda Mahasiswa (KTM) kamu sudah kami terima. Selamat datang, mahasiswa TSU!'
                : 'Terima kasih! Pengaturan akun sudah lengkap. Selamat menjelajah!');
    }
}
