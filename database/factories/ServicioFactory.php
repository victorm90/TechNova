<?php

namespace Database\Factories;

use App\Models\Servicio;
use App\Models\UnidadMedida;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Servicio>
 */
class ServicioFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => ucfirst(fake()->unique()->words(3, true)),
            'unidad_medida_id' => UnidadMedida::factory(),
            'precio_unitario' => fake()->randomFloat(2, 0, 5000),
            'activo' => true,
        ];
    }

    /**
     * Servicio inactivo.
     */
    public function inactivo(): static
    {
        return $this->state(fn (): array => ['activo' => false]);
    }
}
