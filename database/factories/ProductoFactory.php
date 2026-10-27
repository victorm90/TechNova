<?php

namespace Database\Factories;

use App\Models\CategoriaProducto;
use App\Models\Producto;
use App\Models\UnidadMedida;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto>
 */
class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $precioCompra = fake()->randomFloat(2, 1, 500);

        return [
            'sku' => strtoupper(fake()->unique()->bothify('PROD-######')),
            'nombre' => ucfirst(fake()->unique()->words(3, true)),
            'descripcion' => fake()->sentence(),
            'categoria_producto_id' => CategoriaProducto::factory(),
            'unidad_medida_id' => UnidadMedida::factory(),
            'precio_compra' => $precioCompra,
            'precio_venta' => round($precioCompra * fake()->randomFloat(2, 1.1, 2), 2),
            'stock_minimo' => fake()->numberBetween(0, 20),
            'activo' => true,
        ];
    }

    /**
     * Producto inactivo.
     */
    public function inactivo(): static
    {
        return $this->state(fn (array $attributes): array => [
            'activo' => false,
        ]);
    }
}
