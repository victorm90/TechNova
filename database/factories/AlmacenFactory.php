<?php

namespace Database\Factories;

use App\Models\Almacen;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Almacen>
 */
class AlmacenFactory extends Factory
{
    protected $model = Almacen::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => 'Almacén '.ucfirst(fake()->unique()->words(2, true)),
            'direccion' => fake()->streetAddress(),
            'telefono' => fake()->numerify('555#######'),
            'activo' => true,
        ];
    }
}
