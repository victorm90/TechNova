<?php

namespace Database\Seeders;

use App\Models\CategoriaProducto;
use Illuminate\Database\Seeder;

class CategoriaProductoSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            ['nombre' => 'Herramientas', 'descripcion' => 'Herramientas manuales y eléctricas.'],
            ['nombre' => 'Empaque', 'descripcion' => 'Cajas, cintas y material de embalaje.'],
            ['nombre' => 'Insumos', 'descripcion' => 'Consumibles de operación diaria.'],
            ['nombre' => 'Equipos', 'descripcion' => 'Equipos y maquinaria de almacén.'],
            ['nombre' => 'Otros', 'descripcion' => 'Productos sin clasificación específica.'],
        ];

        foreach ($categorias as $categoria) {
            CategoriaProducto::query()->updateOrCreate(
                ['nombre' => $categoria['nombre']],
                [
                    'descripcion' => $categoria['descripcion'],
                    'activo' => true,
                ],
            );
        }
    }
}
