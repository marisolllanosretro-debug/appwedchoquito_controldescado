<?php

namespace Database\Factories;

use App\Models\Compra;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompraFactory extends Factory
{
    protected $model = Compra::class;

    public function definition(): array
    {
        $productos = [
            'Harina de trigo 50kg',
            'Azúcar refinada 25kg',
            'Manteca vegetal 20kg',
            'Huevos frescos caja x30',
            'Levadura fresca 1kg',
            'Margarina premium 15kg',
            'Chocolate cobertura 5kg',
            'Fresas frescas 10kg',
            'Crema de leche 5L',
            'Queso crema 2kg',
            'Canela molida 1kg',
            'Vainilla líquida 500ml',
            'Sal refinada 25kg',
            'Mejorador de pan 2kg',
            'Aceite vegetal 20L',
        ];

        $proveedores = [
            'Molinos del Norte S.A.',
            'Distribuidora de Alimentos del Valle',
            'Productos Alimenticios Frescos C.A.',
            'Importadora de Ingredientes Panadería',
            'Suministros Alimenticios Regionales',
            'Mayorista de Harinas y Derivados',
            'Distribuidora de Lácteos Premium',
            'Productos Orgánicos del Campo',
        ];

        $cantidad = fake()->numberBetween(1, 100);
        $precioUnitario = fake()->randomFloat(2, 5, 500);
        $total = $cantidad * $precioUnitario;

        return [
            'proveedor' => fake()->randomElement($proveedores),
            'producto' => fake()->randomElement($productos),
            'cantidad' => $cantidad,
            'precio_unitario' => $precioUnitario,
            'total' => $total,
            'fecha_compra' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'estado' => fake()->randomElement(['completado', 'pendiente', 'cancelado']),
            'observaciones' => fake()->optional(0.7)->sentence(),
        ];
    }

    public function completado(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'completado',
        ]);
    }

    public function pendiente(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'pendiente',
        ]);
    }

    public function cancelado(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'cancelado',
        ]);
    }
}
