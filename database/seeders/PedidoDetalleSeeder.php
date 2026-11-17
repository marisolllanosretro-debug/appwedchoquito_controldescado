<?php

namespace Database\Seeders;

use App\Models\Pedido;
use App\Models\PedidoDetalle;
use Illuminate\Database\Seeder;

class PedidoDetalleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pedido = Pedido::query()->first() ?? Pedido::factory()->create();

        PedidoDetalle::factory()->count(2)->for($pedido)->create();
    }
}
