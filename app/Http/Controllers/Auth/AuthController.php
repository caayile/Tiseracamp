<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

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

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
        }

        $request->session()->regenerate();
        $user = Auth::user();

        if (! $user->isActive()) {
            Auth::logout();

            return back()->withErrors(['email' => 'Akun ditangguhkan. Hubungi admin.']);
        }

        ActivityLog::record($user, 'login');

        return redirect()->intended(route($user->dashboardRoute()));
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
            'password' => ['required', 'confirmed', 'min:8'],
            'role' => ['required', 'in:student,mentor'],
            'phone' => ['nullable', 'string', 'max:30'],
            'expertise' => ['nullable', 'string', 'max:500'],
        ]);

        $otp = (string) random_int(100000, 999999);

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
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(30),
        ]);

        Auth::login($user);
        ActivityLog::record($user, 'register');

        return redirect()
            ->route('verify.show')
            ->with('success', "Akun dibuat. Kode OTP verifikasi: {$otp} (demo — cek juga di halaman verifikasi).");
    }

    public function showVerify(): View|RedirectResponse
    {
        if (auth()->user()?->email_verified_at) {
            return redirect()->route(auth()->user()->dashboardRoute());
        }

        return view('auth.verify');
    }

    public function verify(Request $request): RedirectResponse
    {
        $data = $request->validate(['otp_code' => ['required', 'digits:6']]);
        $user = $request->user();

        if ($user->otp_code !== $data['otp_code'] || ($user->otp_expires_at && $user->otp_expires_at->isPast())) {
            return back()->withErrors(['otp_code' => 'OTP salah atau sudah kedaluwarsa.']);
        }

        $user->update([
            'email_verified_at' => now(),
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);

        return redirect()->route($user->dashboardRoute())->with('success', 'Email berhasil diverifikasi.');
    }

    public function resendOtp(Request $request): RedirectResponse
    {
        $otp = (string) random_int(100000, 999999);
        $request->user()->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(30),
        ]);

        return back()->with('success', "OTP baru: {$otp}");
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

        return redirect()
            ->route('password.reset', ['token' => $token, 'email' => $user->email])
            ->with('success', 'Token reset dibuat (mode demo). Silakan atur password baru.');
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
            'password' => ['required', 'confirmed', 'min:8'],
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
}
