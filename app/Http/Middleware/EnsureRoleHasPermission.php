<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoleHasPermission
{
    public function handle(Request $request, Closure $next, string $permiso): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        $rol = $user->rol;

        if (! $rol) {
            abort(403);
        }

        $parts = explode('_', $permiso, 2);
        $accion = $parts[0];
        $modulo = $parts[1] ?? null;

        if (! $modulo || ! in_array($modulo, Role::MODULOS, true)) {
            return $next($request);
        }

        $nivelRequerido = $accion === 'gestionar' ? 'gestionar' : 'ver';

        if (! $rol->tienePermiso($modulo, $nivelRequerido)) {
            abort(403);
        }

        return $next($request);
    }
}
