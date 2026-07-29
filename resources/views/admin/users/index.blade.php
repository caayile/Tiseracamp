@extends('layouts.admin')

@section('title', 'Users')
@section('heading', 'Kelola User')

@section('content')
<div class="grid gap-6 lg:grid-cols-[1fr_320px]">
    <div class="card-soft overflow-hidden">
        <div class="border-b border-brand/10 px-5 py-4">
            <form method="GET" class="flex flex-wrap gap-2">
                <select name="role" class="input-field w-auto" onchange="this.form.submit()">
                    <option value="">Semua role</option>
                    @foreach (['student', 'mentor', 'admin'] as $role)
                        <option value="{{ $role }}" @selected(request('role') === $role)>{{ ucfirst($role) }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        <table class="min-w-full text-left text-sm">
            <thead class="bg-brand-mist/60 text-ink-soft">
                <tr>
                    <th class="px-5 py-3 font-medium">Nama</th>
                    <th class="px-5 py-3 font-medium">Email</th>
                    <th class="px-5 py-3 font-medium">Role</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 font-medium"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr class="border-t border-brand/10">
                        <td class="px-5 py-3 font-medium">{{ $user->name }}</td>
                        <td class="px-5 py-3">{{ $user->email }}</td>
                        <td class="px-5 py-3">
                            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="flex items-center gap-2">
                                @csrf
                                @method('PUT')
                                <select name="role" class="input-field w-auto py-1 text-xs">
                                    @foreach (['student', 'mentor', 'admin'] as $role)
                                        <option value="{{ $role }}" @selected($user->role === $role)>{{ ucfirst($role) }}</option>
                                    @endforeach
                                </select>
                                <select name="status" class="input-field w-auto py-1 text-xs">
                                    <option value="active" @selected($user->status === 'active')>Active</option>
                                    <option value="suspended" @selected($user->status === 'suspended')>Suspended</option>
                                </select>
                                <button class="btn-ghost text-xs" type="submit">Update</button>
                            </form>
                        </td>
                        <td class="px-5 py-3"><span class="badge">{{ $user->status }}</span></td>
                        <td class="px-5 py-3">
                            @if ($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Hapus user ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn-ghost text-xs text-red-600" type="submit">Hapus</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-5 py-4">{{ $users->links() }}</div>
    </div>

    <form method="POST" action="{{ route('admin.users.store') }}" class="card-soft h-fit space-y-4 p-5">
        @csrf
        <h2 class="font-display text-lg font-semibold">Buat user</h2>
        <div>
            <label class="mb-1 block text-sm font-medium">Nama</label>
            <input type="text" name="name" class="input-field" required>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Email</label>
            <input type="email" name="email" class="input-field" required>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Password</label>
            <input type="password" name="password" class="input-field" required>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Role</label>
            <select name="role" class="input-field" required>
                <option value="student">Student</option>
                <option value="mentor">Mentor</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <button class="btn-primary w-full" type="submit">Buat user</button>
    </form>
</div>
@endsection
