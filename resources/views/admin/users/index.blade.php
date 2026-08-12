@extends('layouts.admin')

@section('title', 'Users')
@section('heading', 'Kelola User')

@section('content')
<div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_300px]">
    <div class="card-soft min-w-0 overflow-hidden">
        <div class="border-b border-brand/10 px-5 py-4">
            <form method="GET" class="flex flex-wrap gap-2">
                <select name="role" class="input-field w-auto" onchange="this.form.submit()">
                    <option value="">Semua role</option>
                    @foreach (['student', 'mentor', 'admin'] as $role)
                        <option value="{{ $role }}" @selected(request('role') === $role)>{{ ucfirst($role) }}</option>
                    @endforeach
                </select>
                <select name="tsu" class="input-field w-auto" onchange="this.form.submit()">
                    <option value="">Semua</option>
                    <option value="tsu" @selected(request('tsu') === 'tsu')>Mahasiswa TSU</option>
                    <option value="non_tsu" @selected(request('tsu') === 'non_tsu')>Pengguna umum</option>
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-left text-sm">
                <thead class="bg-brand-mist/60 text-ink-soft">
                    <tr>
                        <th class="px-4 py-3 font-medium">Nama</th>
                        <th class="px-4 py-3 font-medium">Email</th>
                        <th class="px-4 py-3 font-medium">Role</th>
                        <th class="px-4 py-3 font-medium">Tipe pengguna</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr class="border-t border-brand/10">
                            <td class="px-4 py-3 font-medium text-ink">{{ $user->name }}</td>
                            <td class="max-w-[200px] truncate px-4 py-3 text-ink-soft" title="{{ $user->email }}">{{ $user->email }}</td>                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('admin.users.update', $user) }}" id="user-update-{{ $user->id }}">
                                    @csrf
                                    @method('PUT')
                                    <select name="role" class="input-field w-auto min-w-[7.5rem] py-1.5 text-xs">
                                        @foreach (['student', 'mentor', 'admin'] as $role)
                                            <option value="{{ $role }}" @selected($user->role === $role)>{{ ucfirst($role) }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td class="px-4 py-3">
                                @if ($user->isTsuStudent())
                                    <span class="badge bg-brand/15 text-brand-dark ring-brand/30">TSU</span>
                                @else
                                    <span class="badge">Umum</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <select name="status" form="user-update-{{ $user->id }}" class="input-field w-auto min-w-[7.5rem] py-1.5 text-xs">
                                        <option value="active" @selected($user->status === 'active')>Active</option>
                                        <option value="suspended" @selected($user->status === 'suspended')>Suspended</option>
                                    </select>
                                    <span class="badge">{{ $user->status }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    <button class="btn-ghost text-xs" type="submit" form="user-update-{{ $user->id }}">Update</button>
                                    @if ($user->isTsuStudent())
                                        <form method="POST" action="{{ route('admin.users.revoke-tsu', $user) }}" onsubmit="return confirm('Cabut status TSU {{ $user->name }}? Pengguna akan dialihkan ke umum dan harus screening ulang.')">
                                            @csrf
                                            <button class="btn-ghost text-xs text-amber-600" type="submit">Cabut TSU</button>
                                        </form>
                                    @endif
                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Hapus user ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn-ghost text-xs text-red-600" type="submit">Hapus</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
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
