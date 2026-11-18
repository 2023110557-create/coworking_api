<?php

namespace Database\Seeders;

use App\Models\SpaceType;
use Illuminate\Database\Seeder;

class SpaceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Sala', 'description' => 'Salas de reunión y conferencias equipadas con pantalla y pizarra'],
            ['name' => 'Escritorio', 'description' => 'Escritorios flexibles en área compartida'],
            ['name' => 'Cabina', 'description' => 'Cabinas privadas e insonorizadas para trabajo individual o en pareja'],
        ];

        foreach ($types as $type) {
            SpaceType::updateOrCreate(['name' => $type['name']], ['description' => $type['description']]);
        }
    }
}
