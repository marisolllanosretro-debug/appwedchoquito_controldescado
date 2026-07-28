<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsuariosSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Admin', 'email' => 'admin@isinuta.test', 'password' => bcrypt('admin1234'), 'id_rol' => User::ROL_ADMINISTRADOR],
            ['name' => 'Cajera', 'email' => 'cajera@isinuta.test', 'password' => bcrypt('cajera1234'), 'id_rol' => User::ROL_CAJERA],
            ['name' => 'Operador', 'email' => 'operador@isinuta.test', 'password' => bcrypt('operador1234'), 'id_rol' => User::ROL_OPERADOR],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => $data['password'],
                    'id_rol' => $data['id_rol'],
                ]
            );
        }
    }
}
