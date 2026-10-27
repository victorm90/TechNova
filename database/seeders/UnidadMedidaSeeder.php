<?php

namespace Database\Seeders;

use App\Models\UnidadMedida;
use Illuminate\Database\Seeder;

class UnidadMedidaSeeder extends Seeder
{
    public function run(): void
    {
        $unidades = [
            ['nombre' => 'Metro', 'abreviatura' => 'm'],
            ['nombre' => 'Centímetro', 'abreviatura' => 'cm'],
            ['nombre' => 'Kilogramo', 'abreviatura' => 'kg'],
            ['nombre' => 'Gramo', 'abreviatura' => 'g'],
            ['nombre' => 'Libra', 'abreviatura' => 'lb'],
            ['nombre' => 'Litro', 'abreviatura' => 'l'],
            ['nombre' => 'Hora', 'abreviatura' => 'h'],
            ['nombre' => 'Unidad', 'abreviatura' => 'ud'],
            ['nombre' => 'Tonelada', 'abreviatura' => 't'],
        ];

        foreach ($unidades as $unidad) {
            UnidadMedida::updateOrCreate(
                ['nombre' => $unidad['nombre']],
                ['abreviatura' => $unidad['abreviatura']],
            );
        }
    }
}
