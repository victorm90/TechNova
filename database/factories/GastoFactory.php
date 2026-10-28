<?php

namespace Database\Factories;

use App\Models\Gasto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Gasto>
 */
class GastoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => ucfirst(fake()->unique()->words(3, true)),
            'precio_unitario' => fake()->randomFloat(2, 0, 5000),
            'activo' => true,
        ];
    }

    /**
     * Gasto inactivo.
     */
    public function inactivo(): static
    {
        return $this->state(fn (): array => ['activo' => false]);
    }
}
