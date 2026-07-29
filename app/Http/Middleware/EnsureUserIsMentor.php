<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsMentor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isMentor() || ! $user->isActive()) {
            abort(403, 'Akses khusus mentor.');
        }

        return $next($request);
    }
}
