<?php

namespace Database\Factories;

use App\Models\Almacen;
use App\Models\Inventario;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inventario>
 */
class InventarioFactory extends Factory
{
    protected $model = Inventario::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'producto_id' => Producto::factory(),
            'almacen_id' => Almacen::factory(),
            'cantidad' => fake()->numberBetween(0, 200),
            'costo_promedio' => fake()->randomFloat(4, 1, 200),
            'ultimo_movimiento_at' => now(),
       