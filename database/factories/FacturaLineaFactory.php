<?php

namespace Database\Factories;

use App\Models\Factura;
use App\Models\FacturaLinea;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FacturaLinea>
 */
class FacturaLineaFactory extends Factory
{
    protected $model = FacturaLinea::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cantidad = fake()->numberBetween(1, 5);
        $precio = fake()->randomFloat(2, 5, 500);

        return [
            'factura_id' => Factura::factory(),
            'tipo' => FacturaLinea::TIPO_SERVICIO,
            'descripcion' => ucfirst(fake()->words(3, true)),
            'cantidad' => $cantidad,
            'precio_unitario' => $precio,
            'subtotal' => round($cantidad * $precio, 2),
            'servicio_id' => null,
            'gasto_id' => null,
            'producto_id' => null,
            'orden' => 0,
        ];
    }
}
