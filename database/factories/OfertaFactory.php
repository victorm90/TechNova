<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Oferta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Oferta>
 */
class OfertaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'codigo' => fake()->unique()->numerify('OF-######'),
            'customer_id' => Customer::factory(),
            'fecha' => fake()->date(),
            'estado' => Oferta::ESTADO_BORRADOR,
        ];
    }

    /**
     * Oferta enviada al cliente.
     */
    public function enviada(): static
    {
        return $this->state(fn (): array => ['estado' => Oferta::ESTADO_ENVIADA]);
    }

    /**
     * Oferta aceptada por el cliente.
     */
    public function aceptada(): static
    {
        return $this->state(fn (): array => ['estado' => Oferta::ESTADO_ACEPTADA]);
    }
}
