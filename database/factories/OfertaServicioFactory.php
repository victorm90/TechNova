<?php

namespace Database\Factories;

use App\Models\Oferta;
use App\Models\OfertaServicio;
use App\Models\Servicio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OfertaServicio>
 */
class OfertaServicioFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'oferta_id' => Oferta::factory(),
            'servicio_id' => Servicio::factory(),
            'cantidad' => 1,
            'precio_unitario' => fake()->randomFloat(2, 0, 1000),
        ];
    }
}
