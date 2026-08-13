@auth
    @if (auth()->user()->isStudent() && auth()->user()->isTsuPending())
        <div class="border-b border-amber-200 bg-amber-50">
            <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-3">
                <p class="text-sm text-amber-900">
                    <span class="font-semibold">KTM sedang dicek admin.</span>
                    Kamu sudah bisa login. Fitur khusus TSU (lowongan TS Group, magang internal) aktif setelah KTM disetujui.
                </p>
                <a href="{{ route('profile.edit') }}" class="text-xs font-semibold text-amber-800 underline hover:no-underline">Lihat KTM</a>
            </div>
        </div>
    @endif
@endauth
