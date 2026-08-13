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
            'tsu_status' => ['nullable', 'required_if:is_tsu,1', 'in:active,fresh_graduate'],
            'semester' => [
                'nullable',
                'required_if:tsu_status,active',
                'integer',
                'min:1',
                'max:14',
            ],
            'ktm' => [
                'nullable',
                'required_if:is_tsu,1',
                'file',
                'mimes:png,jpg,jpeg,pdf',
                'max:5120',
            ],
        ], [
            'tsu_status.required_if' => 'Pilih Mahasiswa Aktif atau Fresh Graduate.',
            'semester.required_if' => 'Isi semester saat ini.',
            'ktm.required_if' => 'Unggah KTM dulu.',
        ]);

        $isTsu = $data['is_tsu'] === '1';
        $tsuStatus = $isTsu ? ($data['tsu_status'] ?? null) : null;
        $semester = $isTsu && $tsuStatus === 'active'
            ? (string) $data['semester']
            : ($isTsu ? null : $user->semester);

        $ktmPath = $user->ktm_path;
        if ($isTsu && $request->hasFile('ktm')) {
            $ktmPath = $request->file('ktm')->store('ktm', media_disk());
        } elseif (! $isTsu) {
            $ktmPath = null;
        }

        $user->forceFill([
            'is_tsu' => $isTsu,
            'tsu_status' => $tsuStatus,
            'semester' => $semester,
            'ktm_path' => $ktmPath,
            'tsu_verified_at' => null,
            'screening_completed_at' => $user->screening_completed_at ?? now(),
        ])->save();

        award_achievement($user, 'screening_done');

        if ($isTsu) {
            notify_admins(
                'KTM menunggu verifikasi',
                $user->name.' mengajukan status TSU ('.$user->tsuStatusLabel().').',
                'info',
                route('admin.users.index', ['tsu' => 'pending'])
            );
        }

        return redirect()
            ->route('dashboard')
            ->with('success', $isTsu
                ? 'KTM sudah kami terima. Kamu bisa login dan memakai fitur umum. Fitur khusus TSU aktif setelah admin menyetujui KTM.'
                : 'Terima kasih! Pengaturan akun sudah lengkap. Selamat menjelajah!');
    }
}
