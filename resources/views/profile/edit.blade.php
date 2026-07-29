@extends(auth()->user()->isMentor() ? 'layouts.mentor' : 'layouts.app')

@section('title', 'Edit Profil')
@section('heading', 'Edit Profil')

@section('content')
@php
    $isMentor = auth()->user()->isMentor();
    $initials = collect(explode(' ', $user->name))->map(fn ($word) => mb_substr($word, 0, 1))->take(2)->implode('');
@endphp

@unless($isMentor)
<section class="mesh-bg border-b border-brand/10">
    <div class="mx-auto flex max-w-3xl items-center gap-4 px-4 py-10">
        @if ($user->avatar)
            <img src="{{ asset('storage/'.$user->avatar) }}" alt="{{ $user->name }}" class="h-16 w-16 rounded-2xl object-cover ring-2 ring-brand/30">
        @else
            <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-brand to-brand-deeper font-display text-lg font-bold text-white">
                {{ strtoupper($initials) }}
            </span>
        @endif
        <div>
            <h1 class="section-title">Edit profil</h1>
            <p class="mt-1 text-sm text-ink-soft">{{ $user->email }} · {{ ucfirst($user->role) }}</p>
        </div>
    </div>
</section>
@endunless

<section class="{{ $isMentor ? 'mx-auto max-w-2xl' : 'mx-auto max-w-3xl px-4 py-10' }}">
    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="card-soft space-y-4 p-6">
        @csrf
        @method('PUT')

        @if ($isMentor)
            <div class="flex items-center gap-4 border-b border-brand/10 pb-4">
                @if ($user->avatar)
                    <img src="{{ asset('storage/'.$user->avatar) }}" alt="{{ $user->name }}" class="h-16 w-16 rounded-2xl object-cover">
                @else
                    <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-brand to-brand-deeper font-display text-lg font-bold text-white">
                        {{ strtoupper($initials) }}
                    </span>
                @endif
                <div>
                    <p class="font-display text-lg font-semibold">{{ $user->name }}</p>
                    <span class="badge">{{ ucfirst($user->role) }}</span>
                </div>
            </div>
        @endif

        <div>
            <label class="mb-1.5 block text-sm font-medium">Foto profil</label>
            <input type="file" name="avatar" accept="image/*" class="input-field file:mr-3 file:rounded-lg file:border-0 file:bg-brand/20 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-brand-deeper">
            @error('avatar') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-medium">Nama</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="input-field" required>
            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-medium">Email</label>
            <input type="email" value="{{ $user->email }}" class="input-field bg-brand-mist/50" disabled>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-medium">No. telepon</label>
                <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" class="input-field">
                @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            @unless($isMentor)
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Jenjang</label>
                    <select name="education_level" class="input-field">
                        <option value="">Pilih jenjang</option>
                        @foreach (['D3', 'D4', 'S1'] as $level)
                            <option value="{{ $level }}" @selected(old('education_level', $user->education_level) === $level)>{{ $level }}</option>
                        @endforeach
                    </select>
                    @error('education_level') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Semester / tingkat</label>
                    <input type="text" name="semester" value="{{ old('semester', $user->semester) }}" class="input-field" placeholder="Semester 6">
                    @error('semester') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Universitas</label>
                    <input type="text" name="university" value="{{ old('university', $user->university) }}" class="input-field">
                    @error('university') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Jurusan</label>
                    <input type="text" name="major" value="{{ old('major', $user->major) }}" class="input-field">
                    @error('major') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            @endunless
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-medium">Bio</label>
            <textarea name="bio" rows="3" class="input-field">{{ old('bio', $user->bio) }}</textarea>
            @error('bio') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        @if ($isMentor)
            <div>
                <label class="mb-1.5 block text-sm font-medium">Keahlian</label>
                <input type="text" name="expertise" value="{{ old('expertise', collect($user->expertise ?? [])->implode(', ')) }}" class="input-field" placeholder="Laravel, React, UI/UX">
                @error('expertise') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        @endif

        <div class="border-t border-brand/10 pt-4">
            <p class="mb-3 text-sm font-semibold text-ink">Ubah password</p>
            <div class="space-y-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Password baru</label>
                    <input type="password" name="password" class="input-field">
                    @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Konfirmasi password</label>
                    <input type="password" name="password_confirmation" class="input-field">
                </div>
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <button class="btn-primary" type="submit">Simpan perubahan</button>
            <a href="{{ $isMentor ? route('mentor.dashboard') : route('dashboard') }}" class="btn-secondary">Batal</a>
        </div>
    </form>
</section>
@endsection
