<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use App\Mail\VerifyAccountMail;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Throwable;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Terima juga "siswa@tigaserangkai" → siswa@tigaserangkai.test
        if (preg_match('/^[^@\s]+@tigaserangkai$/i', $credentials['email'])) {
            $credentials['email'] .= '.test';
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau password salah. Coba siswa@tigaserangkai.test / password'])->onlyInput('email');
        }

        $request->session()->regenerate();
        $user = Auth::user();

        if (! $user->isActive()) {
            Auth::logout();

            return back()->withErrors(['email' => 'Akun ditangguhkan. Hubungi admin.']);
        }

        ActivityLog::record($user, 'login');

        if (! $user->email_verified_at) {
            return redirect()->route('verify.show');
        }

        // Selalu ke dashboard sesuai role (hindari intended yang salah role)
        return redirect()->route($user->dashboardRoute());
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'in:student,mentor'],
            'phone' => ['nullable', 'string', 'max:30'],
            'expertise' => ['nullable', 'string', 'max:500'],
            'terms' => ['accepted'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
            'phone' => $data['phone'] ?? null,
            'expertise' => $data['role'] === 'mentor'
                ? array_values(array_filter(array_map('trim', explode(',', $data['expertise'] ?? ''))))
                : null,
            'status' => 'active',
        ]);

        Auth::login($user);
        ActivityLog::record($user, 'register');

        $mailSent = $this->sendVerificationMail($user);

        $message = $mailSent
            ? 'Akun berhasil dibuat. Cek inbox email kamu (dan folder spam) lalu klik Verifikasi Akun.'
            : 'Akun berhasil dibuat, tapi email verifikasi belum terkirim. Silakan klik kirim ulang di halaman berikutnya.';

        return redirect()
            ->route('verify.show')
            ->with('success', $message);
    }

    public function showVerify(): View|RedirectResponse
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->email_verified_at) {
            return redirect()->route($user->dashboardRoute());
        }

        return view('auth.verify');
    }

    public function verifyFromLink(Request $request, int $id, string $hash): RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Link verifikasi tidak valid atau sudah kedaluwarsa. Silakan login lalu kirim ulang email.']);
        }

        $user = User::findOrFail($id);

        if (! hash_equals($hash, sha1($user->email))) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Link verifikasi tidak valid.']);
        }

        if (! $user->email_verified_at) {
            $user->forceFill([
                'email_verified_at' => now(),
                'otp_code' => null,
                'otp_expires_at' => null,
            ])->save();
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route($user->dashboardRoute())
            ->with('success', 'Email berhasil diverifikasi. Selamat datang!');
    }

    public function resendVerification(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->email_verified_at) {
            return redirect()->route($user->dashboardRoute());
        }

        $mailSent = $this->sendVerificationMail($user);

        return $mailSent
            ? back()->with('success', 'Email verifikasi sudah dikirim ulang. Cek inbox, dan jangan lupa buka folder Spam jika belum muncul.')
            : back()->withErrors(['email' => 'Gagal mengirim email saat ini. Coba lagi beberapa saat lagi.']);
    }

    public function showForgot(): View
    {
        return view('auth.forgot');
    }

    public function sendReset(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        $user = User::where('email', $data['email'])->first();

        if (! $user) {
            return back()->withErrors(['email' => 'Email tidak ditemukan.'])->onlyInput('email');
        }

        if (! $this->sendPasswordOtp($user)) {
            return back()->withErrors(['email' => 'Gagal mengirim OTP. Coba lagi beberapa saat lagi.'])->onlyInput('email');
        }

        $request->session()->put('password_reset_email', $user->email);
        $request->session()->forget('password_reset_verified');

        return redirect()
            ->route('password.otp')
            ->with('success', 'Kode OTP sudah dikirim. Cek inbox email, dan jangan lupa buka folder Spam jika belum muncul.');
    }

    public function showOtp(Request $request): View|RedirectResponse
    {
        $email = $request->session()->get('password_reset_email');

        if (! $email) {
            return redirect()->route('password.request');
        }

        if ($request->session()->get('password_reset_verified')) {
            return redirect()->route('password.reset');
        }

        return view('auth.forgot-otp', ['email' => $email]);
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $email = $request->session()->get('password_reset_email');

        if (! $email) {
            return redirect()->route('password.request');
        }

        $data = $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $user = User::where('email', $email)->first();

        if (! $user || ! $user->otp_code || ! $user->otp_expires_at) {
            return back()->withErrors(['otp' => 'OTP tidak ditemukan. Silakan kirim ulang.']);
        }

        if ($user->otp_expires_at->isPast()) {
            return back()->withErrors(['otp' => 'OTP sudah kedaluwarsa. Silakan kirim ulang.']);
        }

        if (! hash_equals((string) $user->otp_code, (string) $data['otp'])) {
            return back()->withErrors(['otp' => 'Kode OTP salah. Coba lagi.']);
        }

        $user->forceFill([
            'otp_code' => null,
            'otp_expires_at' => null,
        ])->save();

        $request->session()->put('password_reset_verified', true);

        return redirect()
            ->route('password.reset')
            ->with('success', 'OTP berhasil diverifikasi. Silakan buat password baru.');
    }

    public function resendOtp(Request $request): RedirectResponse
    {
        $email = $request->session()->get('password_reset_email');

        if (! $email) {
            return redirect()->route('password.request');
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            return redirect()->route('password.request')->withErrors(['email' => 'Email tidak ditemukan.']);
        }

        if (! $this->sendPasswordOtp($user)) {
            return back()->withErrors(['email' => 'Gagal mengirim OTP. Coba lagi beberapa saat lagi.']);
        }

        $request->session()->forget('password_reset_verified');

        return back()->with('success', 'Kode OTP baru sudah dikirim. Cek inbox dan folder Spam jika belum muncul.');
    }

    public function showReset(Request $request): View|RedirectResponse
    {
        $email = $request->session()->get('password_reset_email');
        $verified = $request->session()->get('password_reset_verified');

        if (! $email) {
            return redirect()->route('password.request');
        }

        if (! $verified) {
            return redirect()->route('password.otp');
        }

        return view('auth.reset', ['email' => $email]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $email = $request->session()->get('password_reset_email');
        $verified = $request->session()->get('password_reset_verified');

        if (! $email || ! $verified) {
            return redirect()->route('password.request');
        }

        $data = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::where('email', $email)->first();

        if (! $user) {
            return redirect()->route('password.request')->withErrors(['email' => 'Email tidak ditemukan.']);
        }

        $user->forceFill([
            'password' => $data['password'],
            'otp_code' => null,
            'otp_expires_at' => null,
        ])->save();

        $request->session()->forget(['password_reset_email', 'password_reset_verified']);

        return redirect()->route('login')->with('success', 'Password berhasil diubah. Silakan masuk dengan password baru.');
    }

    public function logout(Request $request): RedirectResponse
    {
        ActivityLog::record($request->user(), 'logout');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function sendPasswordOtp(User $user): bool
    {
        $otp = (string) random_int(100000, 999999);
        $expiresMinutes = 10;

        $user->forceFill([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes($expiresMinutes),
        ])->save();

        try {
            Mail::to($user->email)->send(new ResetPasswordMail($user, $otp, $expiresMinutes));

            return true;
        } catch (Throwable $e) {
            Log::error('Reset password OTP mail failed', [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function sendVerificationMail(User $user): bool
    {
        $verifyUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        try {
            Mail::to($user->email)->send(new VerifyAccountMail($user, $verifyUrl));

            return true;
        } catch (Throwable $e) {
            Log::error('Verification mail failed', [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
