<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user()?->loadMissing('rol');

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
                'rol' => $user ? [
                    'id_rol' => $user->id_rol,
                    'nombre' => $user->rol?->nombre,
                ] : null,
                'permisos' => $user && $user->rol ? [
                    'ver_clientes' => $user->rol->puedeVer('clientes'),
                    'gestionar_clientes' => $user->rol->puedeGestionar('clientes'),
                    'ver_compras' => $user->rol->puedeVer('compras'),
                    'gestionar_compras' => $user->rol->puedeGestionar('compras'),
                    'ver_proveedores' => $user->rol->puedeVer('proveedores'),
                    'gestionar_proveedores' => $user->rol->puedeGestionar('proveedores'),
                    'ver_productos' => $user->rol->puedeVer('productos'),
                    'gestionar_productos' => $user->rol->puedeGestionar('productos'),
                    'gestionar_categorias' => $user->rol->puedeGestionar('categorias'),
                    'gestionar_usuarios' => $user->rol->puedeGestionar('usuarios'),
                    'gestionar_roles' => $user->rol->puedeGestionar('roles'),
                ] : null,
            ],
            'flash' => [
                'welcome' => fn () => $request->session()->get('welcome'),
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
