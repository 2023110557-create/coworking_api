<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // name (inglés, se usa en el código) => label (español, se muestra)
        $permissions = [
            // Espacios
            'spaces.view' => 'Ver catálogo de espacios',
            'spaces.create' => 'Crear espacios',
            'spaces.update' => 'Editar espacios',
            'spaces.delete' => 'Eliminar espacios',

            // Tipos de espacio
            'space_types.view' => 'Ver tipos de espacio',
            'space_types.create' => 'Crear tipos de espacio',
            'space_types.update' => 'Editar tipos de espacio',
            'space_types.delete' => 'Eliminar tipos de espacio',

            // Reservas
            'reservations.create' => 'Crear reservas',
            'reservations.view_own' => 'Ver historial de reservas propias',
            'reservations.cancel_own' => 'Cancelar reservas propias',
            'reservations.view_pending' => 'Ver reservas pendientes',
            'reservations.approve' => 'Confirmar reservas',
            'reservations.reject' => 'Rechazar reservas',
            'reservations.complete' => 'Marcar reservas como completadas',
            'reservations.view_occupancy' => 'Ver ocupación de espacios por día',

            // Usuarios
            'users.view' => 'Ver usuarios',
            'users.create' => 'Crear usuarios',
            'users.update' => 'Editar usuarios y asignar roles',
            'users.delete' => 'Eliminar usuarios',

            // Roles y permisos
            'roles.view' => 'Ver roles',
            'roles.create' => 'Crear roles',
            'roles.update' => 'Editar roles y asignar permisos',
            'roles.delete' => 'Eliminar roles',
            'permissions.view' => 'Ver permisos',
        ];

        foreach ($permissions as $name => $label) {
            Permission::updateOrCreate(['name' => $name], ['label' => $label]);
        }

        // Roles
        $admin = Role::updateOrCreate(['name' => 'admin'], ['label' => 'Administrador']);
        $staff = Role::updateOrCreate(['name' => 'staff'], ['label' => 'Recepción']);
        $client = Role::updateOrCreate(['name' => 'client'], ['label' => 'Cliente']);

        // Admin: todos los permisos
        $admin->permissions()->sync(Permission::pluck('id'));

        // Staff: catálogo y gestión de reservas
        $staff->permissions()->sync(
            Permission::whereIn('name', [
                'spaces.view',
                'reservations.view_pending',
                'reservations.approve',
                'reservations.reject',
                'reservations.complete',
                'reservations.view_occupancy',
            ])->pluck('id')
        );

        // Client: catálogo y sus propias reservas
        $client->permissions()->sync(
            Permission::whereIn('name', [
                'spaces.view',
                'reservations.create',
                'reservations.view_own',
                'reservations.cancel_own',
            ])->pluck('id')
        );
    }
}
