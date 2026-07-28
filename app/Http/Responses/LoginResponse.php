<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): Response
    {
        $user = $request->user();

        $mensaje = match (true) {
            $user->esAdministrador() => 'Bienvenido Administrador, tienes control total del sistema.',
            $user->esCajera() => 'Bienvenida Cajera, listo para atender a los clientes.',
            $user->esOperador() => 'Bienvenido Operador, tu módulo está listo.',
            default => 'Bienvenido al sistema.',
        };

        session()->flash('welcome', $mensaje);

        return $request->wantsJson()
            ? new JsonResponse(['two_factor' => false], 200)
            : redirect()->intended(route('dashboard', absolute: false));
    }
}
