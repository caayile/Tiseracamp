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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
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
            ? 'Akun dibuat. Cek email kamu lalu klik tombol Verifikasi Akun.'
            : 'Akun dibuat, tapi email verifikasi gagal terkirim. Klik kirim ulang di halaman berikutnya.';

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
            ? back()->with('success', 'Email verifikasi sudah dikirim ulang. Cek inbox / spam.')
            : back()->withErrors(['email' => 'Gagal mengirim email. Periksa konfigurasi MAIL di .env.']);
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
            return back()->withErrors(['email' => 'Email tidak ditemukan.']);
        }

        $token = Str::random(64);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => Hash::make($token), 'created_at' => now()]
        );

        $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);

        try {
            Mail::to($user->email)->send(new ResetPasswordMail($user, $resetUrl));
        } catch (Throwable $e) {
            Log::error('Reset password mail failed', [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['email' => 'Gagal mengirim email reset. Periksa konfigurasi MAIL di .env.']);
        }

        return back()->with('success', 'Link reset password sudah dikirim ke email kamu.');
    }

    public function showReset(Request $request, string $token): View
    {
        return view('auth.reset', [
            'token' => $token,
            'email' => $request->string('email')->toString(),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $row = DB::table('password_reset_tokens')->where('email', $data['email'])->first();

        if (! $row || ! Hash::check($data['token'], $row->token)) {
            return back()->withErrors(['email' => 'Token reset tidak valid.']);
        }

        User::where('email', $data['email'])->update(['password' => $data['password']]);
        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

        return redirect()->route('login')->with('success', 'Password berhasil diubah. Silakan masuk.');
    }

    public function logout(Request $request): RedirectResponse
    {
        ActivityLog::record($request->user(), 'logout');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
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
