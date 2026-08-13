<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        if (! $user->isActive()) {
            auth()->logout();

            return redirect()->route('login')->withErrors([
                'email' => 'Akun kamu sedang ditangguhkan. Hubungi admin.',
            ]);
        }

        $route = $request->route()?->getName();
        $verifyAllowed = ['verify.show', 'verify.resend', 'logout'];
        $screeningAllowed = array_merge($verifyAllowed, ['screening.show', 'screening.store']);

        if (! $user->email_verified_at && ! in_array($route, $verifyAllowed, true)) {
            return redirect()->route('verify.show');
        }

        if ($user->needsScreening() && ! in_array($route, $screeningAllowed, true)) {
            return redirect()->route('screening.show');
        }

        return $next($request);
    }
}
