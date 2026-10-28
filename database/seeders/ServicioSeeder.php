<?php

namespace Database\Seeders;

use App\Models\Servicio;
use App\Models\UnidadMedida;
use Illuminate\Database\Seeder;

class ServicioSeeder extends Seeder
{
    public function run(): void
    {
        $servicios = [
            ['nombre' => 'Mantenimiento general', 'unidad_medida' => 'Unidad', 'precio_unitario' => 150.00],
            ['nombre' => 'Transporte de mercancías', 'unidad_medida' => 'Kilogramo', 'precio_unitario' => 25.00],
            ['nombre' => 'Alquiler de espacio', 'unidad_medida' => 'Metro', 'precio_unitario' => 80.00],
            ['nombre' => 'Servicio de limpieza', 'unidad_medida' => 'Hora', 'precio_unitario' => 12.50],
        ];

        foreach ($servicios as $datosServicio) {
            $unidadMedida = UnidadMedida::query()
                ->where('nombre', $datosServicio['unidad_medida'])
                ->first();

            if (! $unidadMedida) {
                continue;
            }

            Servicio::updateOrCreate(
                ['nombre' => $datosServicio['nombre']],
                [
                    'unidad_medida_id' => $unidadMedida->id,
                    'precio_unitario' => $datosServicio['precio_unitario'],
                    'activo' => true,
                ],
            );
        }
    }
}
