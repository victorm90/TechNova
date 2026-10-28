<?php

namespace App\Services;

use App\Models\Oferta;
use Illuminate\Support\Facades\DB;

class OfertaService
{
    public function __construct(private readonly SecuenciaService $secuencias) {}

    /**
     * Crear una oferta con sus servicios y gastos.
     *
     * @param  array{
     *     customer_id: int,
     *     fecha: string,
     *     estado: string,
     *     servicios?: array<int, array{servicio_id: int|string, cantidad: numeric|string, precio_unitario: numeric|string}>,
     *     gastos?: array<int, array{gasto_id: int|string, cantidad: numeric|string, precio_unitario: numeric|string}>
     * }  $datos
     */
    public function crear(array $datos): Oferta
    {
        return DB::transaction(function () use ($datos): Oferta {
            $oferta = Oferta::create([
                'codigo' => $this->secuencias->siguiente('ofertas', 'OF-'),
                'customer_id' => (int) $datos['customer_id'],
                'fecha' => $datos['fecha'],
                'estado' => $datos['estado'],
            ]);

            $this->guardarLineas(
                $oferta,
                $datos['servicios'] ?? [],
                $datos['gastos'] ?? [],
            );

            return $oferta->load(['customer', 'servicios.servicio', 'gastos.gasto']);
        });
    }

    /**
     * Actualizar la cabecera de una oferta y reemplazar sus líneas.
     *
     * @param  array{
     *     customer_id: int,
     *     fecha: string,
     *     estado: string,
     *     servicios?: array<int, array{servicio_id: int|string, cantidad: numeric|string, precio_unitario: numeric|string}>,
     *     gastos?: array<int, array{gasto_id: int|string, cantidad: numeric|string, precio_unitario: numeric|string}>
     * }  $datos
     */
    public function actualizar(Oferta $oferta, array $datos): Oferta
    {
        return DB::transaction(function () use ($oferta, $datos): Oferta {
            $oferta->update([
                'customer_id' => (int) $datos['customer_id'],
                'fecha' => $datos['fecha'],
                'estado' => $datos['estado'],
            ]);

            $oferta->servicios()->delete();
            $oferta->gastos()->delete();

            $this->guardarLineas(
                $oferta,
                $datos['servicios'] ?? [],
                $datos['gastos'] ?? [],
            );

            return $oferta->refresh()->load(['customer', 'servicios.servicio', 'gastos.gasto']);
        });
    }

    /**
     * Persistir las líneas de servicios y gastos de la oferta.
     *
     * @param  array<int, array{servicio_id: int|string, cantidad: numeric|string, precio_unitario: numeric|string}>  $servicios
     * @param  array<int, array{gasto_id: int|string, cantidad: numeric|string, precio_unitario: numeric|string}>  $gastos
     */
    private function guardarLineas(Oferta $oferta, array $servicios, array $gastos): void
    {
        foreach ($servicios as $linea) {
            $oferta->servicios()->create([
                'servicio_id' => (int) $linea['servicio_id'],
                'cantidad' => round((float) $linea['cantidad'], 2),
                'precio_unitario' => round((float) $linea['precio_unitario'], 2),
            ]);
        }

        foreach ($gastos as $linea) {
            $oferta->gastos()->create([
                'gasto_id' => (int) $linea['gasto_id'],
                'cantidad' => round((float) $linea['cantidad'], 2),
                'precio_unitario' => round((float) $linea['precio_unitario'], 2),
            ]);
        }
    }
}
