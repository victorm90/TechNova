<?php

namespace Database\Seeders;

use App\Models\Almacen;
use Illuminate\Database\Seeder;

class AlmacenSeeder extends Seeder
{
    public function run(): void
    {
        $almacenes = [
            [
                'nombre' => 'Almacén principal',
                'direccion' => 'Calle 12 #45-18, Centro',
                'telefono' => '55501010',
            ],
            [
                'nombre' => 'Almacén secundario',
                'direccion' => 'Avenida Libertad #8-22, Norte',
                'telefono' => '55501011',
            ],
            [
                'nombre' => 'Depósito de tránsito',
                'direccion' => 'Carretera Industrial km 4',
                'telefono' => '55501012',
            ],
        ];

        foreach ($almacenes as $almacen) {
            Almacen::query()->updateOrCreate(
                ['nombre' => $almacen['nombre']],
                [
                    'direccion' => $almacen['direccion'],
                    'telefono' => $almacen['telefono'],
                    'activo' => true,
                ],
            );
        }
    }
}
