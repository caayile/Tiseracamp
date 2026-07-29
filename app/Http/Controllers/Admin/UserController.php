<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->string('role')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8'],
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
}
