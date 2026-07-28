# Auditoría de seguridad — Módulo de usuarios y autenticación

## Resumen ejecutivo

La aplicación implementa una arquitectura de seguridad en capas (Fortify + middleware RBAC + Form Requests) que cumple con los estándares mínimos para una app administrativa. Se identificaron 3 riesgos de severidad media y 3 controles bien implementados. La mejora de mayor impacto recomendada es implementar un middleware que cierre la sesión activa cuando un administrador desactiva a un usuario, complementando la lógica de bloqueo de login ya implementada. No se encontraron vulnerabilidades críticas.

## Controles bien implementados

| Control | Archivo | Descripción |
|---------|---------|-------------|
| Registro público deshabilitado | `config/fortify.php:147` | `Features::registration()` comentado, evita auto-registro |
| Middleware de permisos en rutas | `routes/web.php:43-48` | Toda ruta de usuarios protegida con `permission:usuarios.gestionar` |
| Form Request con authorize() | `app/Http/Requests/StoreUsuarioRequest.php` | Validación + autorización centralizada, no solo inline |

## Tabla de riesgos

| Riesgo | Severidad | Archivo | Recomendación |
|--------|-----------|---------|---------------|
| Sesión activa tras desactivar usuario | Media | `app/Providers/FortifyServiceProvider.php` | Implementar middleware `EnsureUserIsActive` que verifique `isActivo()` en cada request y cierre sesión si el usuario fue desactivado |
| Exposición del modelo User completo en Inertia | Media | `app/Http/Middleware/HandleInertiaRequests.php` | Crear un DTO/resource que solo exponga campos necesarios (name, email, roles, permisos) en lugar del modelo completo |
| Sin UserPolicy para defensa en profundidad | Media | — | Agregar `UserPolicy` como capa adicional de autorización ante cambios en la lógica de rutas |

## Mejora implementada: Middleware EnsureUserIsActive

Implementar un middleware que verifique que el usuario autenticado sigue activo en cada request y fuerce cierre de sesión si fue desactivado.

### Código

```php
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

        if ($user && ! $user->isActivo()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->with('error', 'Tu cuenta ha sido desactivada.');
        }

        return $next($request);
    }
}
```

### Registro en bootstrap/app.php

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => EnsureUserHasRole::class,
        'permission' => EnsureUserHasPermission::class,
        'user.active' => EnsureUserIsActive::class,
    ]);
})
```

### Test

```php
it('inactive user is logged out on next request', function () {
    $user = userWithRole('admin');
    $team = $user->personalTeam();

    $this->actingAs($user);
    $user->update(['activo' => false]);

    $this->get("/{$team->slug}/dashboard")
        ->assertRedirect('/login');
});
```

**Impacto:** Elimina la ventana de seguridad entre desactivación y cierre de sesión manual.
**Costo:** Bajo (un middleware + registro + test).
**Dependencia:** Ninguna — la lógica `isActivo()` ya existe en User.
