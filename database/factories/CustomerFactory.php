<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->lexify('Cliente ??????'),
            'address' => fake()->address(),
            'phone' => fake()->unique()->numerify('########'),
            'identity_card' => fake()->unique()->numerify('###########'),
        ];
    }
}
