<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        if (! $this->isConfigured()) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Login Google belum dikonfigurasi. Isi GOOGLE_CLIENT_ID & GOOGLE_CLIENT_SECRET di file .env.']);
        }

        $role = $request->query('role', 'student');
        if (! in_array($role, ['student', 'mentor'], true)) {
            $role = 'student';
        }

        $request->session()->put('google_oauth_role', $role);

        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        if (! $this->isConfigured()) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Login Google belum dikonfigurasi.']);
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Gagal autentikasi Google. Coba lagi.']);
        }

        $email = $googleUser->getEmail();
        if (! $email) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Akun Google tidak menyediakan email.']);
        }

        $role = $request->session()->pull('google_oauth_role', 'student');
        if (! in_array($role, ['student', 'mentor'], true)) {
            $role = 'student';
        }

        $user = User::query()
            ->where(function ($query) use ($googleUser, $email) {
                $query->where('google_id', $googleUser->getId())
                    ->orWhere('email', $email);
            })
            ->first();

        if ($user) {
            if (! $user->isActive()) {
                return redirect()
                    ->route('login')
                    ->withErrors(['email' => 'Akun ditangguhkan. Hubungi admin.']);
            }

            $user->forceFill([
                'google_id' => $googleUser->getId(),
                'email_verified_at' => $user->email_verified_at ?? now(),
                'avatar' => $user->avatar ?: $googleUser->getAvatar(),
            ])->save();
        } else {
            $request->session()->put('google_oauth_data', [
                'google_id' => $googleUser->getId(),
                'name' => $googleUser->getName() ?: Str::before($email, '@'),
                'email' => $email,
                'avatar' => $googleUser->getAvatar(),
                'role' => $role,
            ]);

            return redirect()
                ->route('register')
                ->with('info', 'Akun belum terdaftar. Silakan lengkapi data berikut untuk membuat akun baru.');
        }

        Auth::login($user, true);
        $request->session()->regenerate();
        ActivityLog::record($user, 'login_google');

        return redirect()->route($user->postAuthRoute());
    }

    private function isConfigured(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'));
    }
}
