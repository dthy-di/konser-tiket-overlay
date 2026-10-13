<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Pemakaian di route: EnsureRole::class.':admin'  atau  EnsureRole::class.':scanner,admin' */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        abort_unless($user->hasRole(...$roles), 403, 'Anda tidak punya akses ke halaman ini.');

        return $next($request);
    }
}
