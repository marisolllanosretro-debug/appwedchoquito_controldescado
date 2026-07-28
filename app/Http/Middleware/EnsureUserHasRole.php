<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        $roleIds = array_map('intval', $roles);

        if (! $user->hasRol(...$roleIds)) {
            abort(403);
        }

        return $next($request);
    }
}
