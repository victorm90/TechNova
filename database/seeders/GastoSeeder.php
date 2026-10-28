<?php

namespace Database\Seeders;

use App\Models\Gasto;
use Illuminate\Database\Seeder;

class GastoSeeder extends Seeder
{
    public function run(): void
    {
        $gastos = [
            ['nombre' => 'Transporte y logística', 'precio_unitario' => 120.00],
            ['nombre' => 'Alquiler de equipos', 'precio_unitario' => 250.00],
            ['nombre' => 'Combustible', 'precio_unitario' => 60.00],
            ['nombre' => 'Materiales auxiliares', 'precio_unitario' => 35.50],
        ];

        foreach ($gastos as $gasto) {
            Gasto::updateOrCreate(
                ['nombre' => $gasto['nombre']],
                [
                    'precio_unitario' => $gasto['precio_unitario'],
                    'activo' => true,
                ],
            );
        }
    }
}
