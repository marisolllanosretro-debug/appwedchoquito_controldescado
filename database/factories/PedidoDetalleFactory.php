<?php

namespace Database\Factories;

use App\Models\Pedido;
use App\Models\PedidoDetalle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PedidoDetalle>
 */
class PedidoDetalleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cantidad = fake()->numberBetween(1, 5);
        $precioUnitario = fake()->randomFloat(2, 5, 100);

        return [
            'pedido_id' => Pedido::factory(),
            'producto_id' => null,
            'producto_nombre' => fake()->words(3, true),
            'cantidad' => $cantidad,
            'precio_unitario' => $precioUnitario,
            'subtotal' => $cantidad * $precioUnitario,
        ];
    }
}
