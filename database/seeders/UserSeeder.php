<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Todos con la contraseña "password123" (el cast "hashed" del modelo la encripta)
        $users = [
            ['name' => 'Administrador Coworking', 'email' => 'admin@coworking.test', 'role' => 'admin'],
            ['name' => 'Recepción Coworking', 'email' => 'staff@coworking.test', 'role' => 'staff'],
            ['name' => 'Cliente Demo', 'email' => 'client@coworking.test', 'role' => 'client'],
            ['name' => 'Cliente Demo 2', 'email' => 'client2@coworking.test', 'role' => 'client'],
        ];

        foreach ($users as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'], 'password' => 'password123']
            );

            $user->roles()->sync([Role::where('name', $data['role'])->value('id')]);
        }
    }
}
