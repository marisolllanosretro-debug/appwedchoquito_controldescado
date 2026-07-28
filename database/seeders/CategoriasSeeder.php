<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriasSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            ['nombre' => 'Panificados', 'descripcion' => 'Pan artesanal, baguettes, pan de molde y más', 'icono' => '🥖'],
            ['nombre' => 'Pizzas', 'descripcion' => 'Pizzas artesanales, pizzetas y calzones', 'icono' => '🍕'],
            ['nombre' => 'Repostería', 'descripcion' => 'Pasteles, cupcakes, galletas y postres', 'icono' => '🧁'],
            ['nombre' => 'Bebidas', 'descripcion' => 'Jugos, refrescos y bebidas calientes', 'icono' => '🥤'],
            ['nombre' => 'Otros', 'descripcion' => 'Productos adicionales y complementos', 'icono' => '📦'],
        ];

        foreach ($categorias as $cat) {
            Categoria::create($cat);
        }
    }
}
