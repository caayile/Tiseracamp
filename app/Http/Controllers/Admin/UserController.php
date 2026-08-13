<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->string('role')))
            ->when($request->string('tsu')->toString() === 'tsu', fn ($q) => $q->where('is_tsu', true)->whereNotNull('tsu_verified_at'))
            ->when($request->string('tsu')->toString() === 'pending', fn ($q) => $q->where('is_tsu', true)->whereNull('tsu_verified_at'))
            ->when($request->string('tsu')->toString() === 'non_tsu', fn ($q) => $q->where(function ($sub) {
                $sub->where('is_tsu', false)->orWhereNull('is_tsu');
            }))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', Password::defaults()],
            'role' => ['required', 'in:student,mentor,admin'],
        ]);

        User::create([
            ...$data,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        ActivityLog::record(auth()->user(), 'create_user', null, $data['email']);

        return back()->with('success', 'User dibuat.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', 'in:student,mentor,admin'],
            'status' => ['required', 'in:active,suspended'],
        ]);

        $user->update($data);
        ActivityLog::record(auth()->user(), 'update_user', $user);

        return back()->with('success', 'User diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if($user->id === auth()->id(), 403);
        $user->delete();

        return back()->with('success', 'User dihapus.');
    }

    public function approveTsu(User $user): RedirectResponse
    {
        abort_unless($user->is_tsu && filled($user->ktm_path), 404);

        $user->update(['tsu_verified_at' => now()]);
        ActivityLog::record(auth()->user(), 'approve_tsu', $user);
        notify_user(
            $user->id,
            'Status TSU disetujui',
            'KTM kamu sudah diverifikasi. Fitur TS Group & magang internal sekarang aktif.',
            'success',
            route('dashboard')
        );

        return back()->with('success', 'Status TSU '.$user->name.' disetujui.');
    }

    public function rejectTsu(User $user): RedirectResponse
    {
        abort_unless($user->is_tsu, 404);

        $user->update([
            'is_tsu' => false,
            'tsu_status' => null,
            'tsu_verified_at' => null,
            'ktm_path' => null,
        ]);
        ActivityLog::record(auth()->user(), 'reject_tsu', $user);
        notify_user(
            $user->id,
            'Pengajuan TSU ditolak',
            'Admin belum dapat memverifikasi KTM. Kamu tetap bisa memakai akun sebagai pengguna umum.',
            'warning',
            route('profile.edit')
        );

        return back()->with('success', 'Pengajuan TSU '.$user->name.' ditolak.');
    }

    public function revokeTsu(User $user): RedirectResponse
    {
        abort_unless($user->isTsuStudent() || $user->isTsuPending(), 404);

        $user->update([
            'is_tsu' => false,
            'tsu_status' => null,
            'tsu_verified_at' => null,
            'screening_completed_at' => null,
            'ktm_path' => null,
        ]);

        ActivityLog::record(auth()->user(), 'revoke_tsu', $user);

        notify_user(
            $user->id,
            'Status TSU dicabut',
            'Status mahasiswa TSU kamu telah dicabut oleh admin. Kamu kini menjadi pengguna umum.',
            'warning'
        );

        return back()->with('success', 'Status TSU '.$user->name.' dicabut, pengguna dialihkan ke umum.');
    }
}
