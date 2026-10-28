<?php

namespace Database\Factories;

use App\Models\OfertaServicio;
use App\Models\OfertaServicioProducto;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OfertaServicioProducto>
 */
class OfertaServicioProductoFactory extends Factory
{
    protected $model = OfertaServicioProducto::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'oferta_servicio_id' => OfertaServicio::factory(),
            'producto_id' => Producto::factory(),
            'cantidad' => fake()->numberBetween(1, 5),
            'precio_venta' => fake()->randomFloat(2, 5, 500),
        ];
    }
}
