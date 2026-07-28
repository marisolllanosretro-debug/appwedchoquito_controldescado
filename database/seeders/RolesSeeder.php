<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        Role::updateOrCreate(['id' => 1], [
            'nombre' => 'admin',
            'descripcion' => 'Administrador del sistema — acceso completo.',
            'permisos' => [
                'clientes' => 'gestionar',
                'compras' => 'gestionar',
                'usuarios' => 'gestionar',
                'roles' => 'gestionar',
            ],
        ]);

        Role::updateOrCreate(['id' => 2], [
            'nombre' => 'cajera',
            'descripcion' => 'Cajera — puede gestionar compras y ver clientes.',
            'permisos' => [
                'clientes' => 'ver',
                'compras' => 'gestionar',
                'usuarios' => 'ninguno',
                'roles' => 'ninguno',
            ],
        ]);

        Role::updateOrCreate(['id' => 3], [
            'nombre' => 'operador',
            'descripcion' => 'Operador — puede consultar clientes y pagos.',
            'permisos' => [
                'clientes' => 'ver',
                'compras' => 'ver',
                'usuarios' => 'ninguno',
                'roles' => 'ninguno',
            ],
        ]);

        Role::updateOrCreate(['id' => 4], [
            'nombre' => 'cliente',
            'descripcion' => 'Cliente — acceso limitado a consulta propia.',
            'permisos' => [
                'clientes' => 'ver',
                'compras' => 'ver',
                'usuarios' => 'ninguno',
                'roles' => 'ninguno',
            ],
        ]);
    }
}
