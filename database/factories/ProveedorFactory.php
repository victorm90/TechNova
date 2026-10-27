<?php

namespace Database\Factories;

use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Proveedor>
 */
class ProveedorFactory extends Factory
{
    protected $model = Proveedor::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => ucfirst(fake()->unique()->company()),
            'contacto' => fake()->name(),
            'telefono' => fake()->numerify('555#######'),
            'email' => fake()->unique()->companyEmail(),
            'direccion' => fake()->address(),
            'identificacion_fiscal' => strtoupper(fake()->unique()->bothify('RIF-########-#')),
            'notas' => fake()->optional()->sentence(),
            'activo' => true,
        ];
    }
}
