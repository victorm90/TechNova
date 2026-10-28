<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Factura;
use App\Models\Oferta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Factura>
 */
class FacturaFactory extends Factory
{
    protected $model = Factura::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'codigo' => fake()->unique()->numerify('FAC-######'),
            'oferta_id' => Oferta::factory(),
            'customer_id' => Customer::factory(),
            'fecha' => fake()->date(),
            'subtotal_servicios' => 0,
            'subtotal_gastos' => 0,
            'subtotal_productos' => 0,
            'total' => 0,
            'nota' => null,
            'forma_pago' => null,
            'observaciones' => null,
            'firma_pie' => null,
            'almacen_id' => null,
            'user_id' => null,
        ];
    }

    /**
     * Factura coherente con la oferta indicada.
     */
    public function forOferta(Oferta $oferta): static
    {
        return $this->state(fn (): array => [
            'oferta_id' => $oferta->id,
            'customer_id' => $oferta->customer_id,
        ]);
    }
}
