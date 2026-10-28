<?php

namespace Database\Factories;

use App\Models\HistorialPrecio;
use App\Models\OfertaServicio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HistorialPrecio>
 */
class HistorialPrecioFactory extends Factory
{
    protected $model = HistorialPrecio::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'historialable_type' => OfertaServicio::class,
            'historialable_id' => OfertaServicio::factory(),
            'precio_anterior' => fake()->randomFloat(2, 1, 100),
            'precio_nuevo' => fake()->randomFloat(2, 1, 100),
            'user_id' => null,
        ];
    }
}
