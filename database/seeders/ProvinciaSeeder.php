<?php

namespace Database\Seeders;

use App\Models\Provincia;
use Illuminate\Database\Seeder;

class ProvinciaSeeder extends Seeder
{
    public function run(): void
    {
        $provincias = [
            ['codigo' => '1', 'nombre' => 'Pinar del Río'],
            ['codigo' => '2', 'nombre' => 'Artemisa'],
            ['codigo' => '3', 'nombre' => 'La Habana'],
            ['codigo' => '4', 'nombre' => 'Mayabeque'],
            ['codigo' => '5', 'nombre' => 'Matanzas'],
            ['codigo' => '6', 'nombre' => 'Villa Clara'],
            ['codigo' => '7', 'nombre' => 'Cienfuegos'],
            ['codigo' => '8', 'nombre' => 'Sancti Spíritus'],
            ['codigo' => '9', 'nombre' => 'Ciego de Ávila'],
            ['codigo' => '10', 'nombre' => 'Camagüey'],
            ['codigo' => '11', 'nombre' => 'Las Tunas'],
            ['codigo' => '12', 'nombre' => 'Holguín'],
            ['codigo' => '13', 'nombre' => 'Granma'],
            ['codigo' => '14', 'nombre' => 'Santiago de Cuba'],
            ['codigo' => '15', 'nombre' => 'Guantánamo'],
            ['codigo' => '16', 'nombre' => 'Isla de la Juventud'],
        ];

        foreach ($provincias as $provincia) {
            Provincia::updateOrCreate(
                ['codigo' => $provincia['codigo']],
                ['nombre' => $provincia['nombre']],
            );
        }
    }
}
