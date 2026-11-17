<?php

namespace App\Actions\Pedidos;

use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegistrarPedido
{
    /**
     * @param  array{
     *     cliente_nombre: string,
     *     cliente_email: string,
     *     cliente_telefono?: string|null,
     *     direccion?: string|null,
     *     metodo_pago: string,
     *     items: list<array{producto_id: int, cantidad: int}>
     * }  $data
     */
    public function execute(array $data, ?User $user): Pedido
    {
        return DB::transaction(function () use ($data, $user): Pedido {
            $cliente = Cliente::query()->updateOrCreate(
                ['email' => $data['cliente_email']],
                [
                    'nombre' => $data['cliente_nombre'],
                    'telefono' => $data['cliente_telefono'] ?? null,
                    'direccion' => $data['direccion'] ?? null,
                ],
            );

            $productos = Producto::query()
                ->whereKey(collect($data['items'])->pluck('producto_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $lineas = [];
            $totalCentavos = 0;

            foreach ($data['items'] as $index => $item) {
                $producto = $productos->get($item['producto_id']);

                if (! $producto) {
                    throw ValidationException::withMessages([
                        "items.$index.producto_id" => 'El producto ya no está disponible.',
                    ]);
                }

                if ($producto->stock < $item['cantidad']) {
                    throw ValidationException::withMessages([
                        "items.$index.cantidad" => "Solo hay {$producto->stock} unidades disponibles de {$producto->nombre}.",
                    ]);
                }

                $precioCentavos = $this->toCents($producto->precio_venta);
                $subtotalCentavos = $precioCentavos * $item['cantidad'];
                $totalCentavos += $subtotalCentavos;

                $lineas[] = [
                    'producto' => $producto,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $this->fromCents($precioCentavos),
                    'subtotal' => $this->fromCents($subtotalCentavos),
                ];
            }

            $pedido = Pedido::query()->create([
                'cliente_id' => $cliente->id,
                'user_id' => $user?->id,
                'numero' => 'PED-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)),
                'cliente_nombre' => $data['cliente_nombre'],
                'cliente_email' => $data['cliente_email'],
                'cliente_telefono' => $data['cliente_telefono'] ?? null,
                'direccion' => $data['direccion'] ?? null,
                'subtotal' => $this->fromCents($totalCentavos),
                'total' => $this->fromCents($totalCentavos),
                'metodo_pago' => $data['metodo_pago'],
                'estado' => 'completado',
                'completado_at' => now(),
            ]);

            foreach ($lineas as $linea) {
                $producto = $linea['producto'];

                $pedido->detalles()->create([
                    'producto_id' => $producto->id,
                    'producto_nombre' => $producto->nombre,
                    'cantidad' => $linea['cantidad'],
                    'precio_unitario' => $linea['precio_unitario'],
                    'subtotal' => $linea['subtotal'],
                ]);

                $producto->decrement('stock', $linea['cantidad']);
            }

            return $pedido->load(['cliente', 'detalles.producto', 'registradoPor']);
        }, 3);
    }

    private function toCents(string|float|int $amount): int
    {
        return (int) round((float) $amount * 100);
    }

    private function fromCents(int $amount): string
    {
        return number_format($amount / 100, 2, '.', '');
    }
}
