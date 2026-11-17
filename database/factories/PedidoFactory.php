<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Pedido>
 */
class PedidoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cliente_id' => Cliente::factory(),
            'user_id' => User::factory(),
            'numero' => 'PED-'.now()->format('Ymd').'-'.Str::upper(fake()->unique()->bothify('??##??##')),
            'cliente_nombre' => fake()->name(),
            'cliente_email' => fake()->unique()->safeEmail(),
            'cliente_telefono' => fake()->phoneNumber(),
            'direccion' => fake()->address(),
            'subtotal' => 0,
            'total' => 0,
            'metodo_pago' => fake()->randomElement(['efectivo', 'transferencia']),
            'estado' => 'completado',
            'completado_at' => now(),
        ];
    }
}
