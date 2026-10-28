<?php

namespace Database\Factories;

use App\Models\Gasto;
use App\Models\Oferta;
use App\Models\OfertaGasto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OfertaGasto>
 */
class OfertaGastoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'oferta_id' => Oferta::factory(),
            'gasto_id' => Gasto::factory(),
            'cantidad' => 1,
            'precio_unitario' => fake()->randomFloat(2, 0, 1000),
        ];
    }
}
