<?php

namespace Database\Seeders;

use App\Models\Pedido;
use App\Models\PedidoDetalle;
use Illuminate\Database\Seeder;

class PedidoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Pedido::factory()
            ->count(5)
            ->create()
            ->each(function (Pedido $pedido): void {
                $detalles = PedidoDetalle::factory()->count(2)->for($pedido)->create();
                $total = $detalles->sum(fn (PedidoDetalle $detalle): float => (float) $detalle->subtotal);

                $pedido->update([
                    'subtotal' => $total,
                    'total' => $total,
                ]);
            });
    }
}
