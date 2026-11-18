<?php

namespace Database\Seeders;

use App\Models\Space;
use App\Models\SpaceType;
use Illuminate\Database\Seeder;

class SpaceSeeder extends Seeder
{
    public function run(): void
    {
        $room = SpaceType::where('name', 'Sala')->value('id');
        $desk = SpaceType::where('name', 'Escritorio')->value('id');
        $booth = SpaceType::where('name', 'Cabina')->value('id');

        $spaces = [
            ['name' => 'Sala de Reuniones A', 'space_type_id' => $room, 'floor' => 1, 'capacity' => 8, 'price_per_hour' => 40.00, 'is_active' => true],
            ['name' => 'Sala de Reuniones B', 'space_type_id' => $room, 'floor' => 1, 'capacity' => 12, 'price_per_hour' => 60.00, 'is_active' => true],
            ['name' => 'Sala de Conferencias', 'space_type_id' => $room, 'floor' => 2, 'capacity' => 20, 'price_per_hour' => 90.00, 'is_active' => true],
            ['name' => 'Escritorio Flexible 1', 'space_type_id' => $desk, 'floor' => 1, 'capacity' => 1, 'price_per_hour' => 8.50, 'is_active' => true],
            ['name' => 'Escritorio Flexible 2', 'space_type_id' => $desk, 'floor' => 1, 'capacity' => 1, 'price_per_hour' => 8.50, 'is_active' => true],
            ['name' => 'Escritorio Flexible 3', 'space_type_id' => $desk, 'floor' => 1, 'capacity' => 1, 'price_per_hour' => 8.50, 'is_active' => true],
            ['name' => 'Cabina Privada 1', 'space_type_id' => $booth, 'floor' => 2, 'capacity' => 2, 'price_per_hour' => 15.00, 'is_active' => true],
            ['name' => 'Cabina Privada 2', 'space_type_id' => $booth, 'floor' => 2, 'capacity' => 2, 'price_per_hour' => 15.00, 'is_active' => true],
            // Inactivo a propósito, para probar el scope active() y los filtros del catálogo
            ['name' => 'Cabina Privada 3', 'space_type_id' => $booth, 'floor' => 2, 'capacity' => 2, 'price_per_hour' => 15.00, 'is_active' => false],
        ];

        foreach ($spaces as $space) {
            Space::updateOrCreate(['name' => $space['name']], $space);
        }
    }
}
