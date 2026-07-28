<?php

use App\Http\Middleware\EnsureRoleHasPermission;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'rol' => EnsureUserHasRole::class,
            'permiso' => EnsureRoleHasPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (AuthorizationException|HttpException $e, Request $request) {
            $status = $e instanceof HttpException ? $e->getStatusCode() : 403;

            if ($request->is('api/*') || $request->expectsJson()) {
                return;
            }

            $messages = [
                403 => 'No tienes permiso para realizar esta acción.',
                404 => 'La página que buscas no existe.',
                419 => 'Tu sesión ha expirado. Inicia sesión nuevamente.',
            ];

            return Inertia::render('Error', [
                'status' => $status,
                'message' => $messages[$status] ?? 'Ocurrió un error inesperado.',
            ])->toResponse($request)->setStatusCode($status);
        });
    })->create();
