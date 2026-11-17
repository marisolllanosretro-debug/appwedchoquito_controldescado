<?php

namespace App\Http\Controllers\Pedidos;

use App\Actions\Pedidos\RegistrarPedido;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePedidoRequest;
use App\Models\Pedido;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PedidoController extends Controller
{
    public function store(StorePedidoRequest $request, RegistrarPedido $registrarPedido): RedirectResponse
    {
        $pedido = $registrarPedido->execute($request->validated(), $request->user());

        if ($request->user() === null) {
            $request->session()->push('pedidos_invitado', $pedido->getRouteKey());
        }

        return to_route('pedidos.show', $pedido)
            ->with('success', 'Compra efectuada correctamente.');
    }

    public function show(Request $request, Pedido $pedido): Response
    {
        $user = $request->user();
        $esPedidoDelUsuario = $user !== null
            && ($pedido->user_id === $user->id || $user->esAdministrador());
        $esPedidoInvitadoDeLaSesion = $pedido->user_id === null
            && in_array(
                $pedido->getRouteKey(),
                $request->session()->get('pedidos_invitado', []),
                true,
            );

        abort_unless(
            $esPedidoDelUsuario || $esPedidoInvitadoDeLaSesion,
            403,
        );

        return Inertia::render('Pedidos/Show', [
            'pedido' => $pedido->load(['detalles.producto', 'registradoPor:id,name']),
        ]);
    }
}
