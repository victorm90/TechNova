<?php

namespace App\Services;

use App\Models\Factura;
use App\Models\FacturaLinea;
use App\Models\Oferta;
use App\Models\OfertaServicio;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FacturacionService
{
    public function __construct(
        private readonly SecuenciaService $secuencias,
        private readonly OfertaService $ofertas,
        private readonly StockOfertaService $stock,
        private readonly InventarioService $inventario,
    ) {}

    /**
     * Facturar una oferta aplicando los ajustes reales confirmados por el usuario.
     *
     * @param  array<string, mixed>  $datos
     */
    public function facturar(Oferta $oferta, array $datos, ?User $usuario = null): Factura
    {
        return DB::transaction(function () use ($oferta, $datos, $usuario): Factura {
            /** @var Oferta $oferta */
            $oferta = Oferta::query()
                ->with(['servicios.productos', 'gastos', 'customer', 'factura'])
                ->lockForUpdate()
                ->findOrFail($oferta->id);

            $this->validarEstado($oferta);
            $this->validarLineasCompletas($oferta, $datos);

            $almacenId = (int) $datos['almacen_id'];

            $this->ofertas->aplicarAjustes($oferta, $datos, $usuario);

            $oferta->load([
                'servicios.servicio.unidadMedida',
                'servicios.productos.producto.unidadMedida',
                'gastos.gasto',
            ]);

            $this->stock->validar(
                $this->ofertas->requerimientosDeOferta($oferta),
                $almacenId,
            );

            $lineas = $this->construirLineas($oferta);
            $totales = $this->totalesPorTipo($lineas);

            $factura = Factura::create([
                'codigo' => $this->secuencias->siguiente('facturas', 'FAC-'),
                'oferta_id' => $oferta->id,
                'customer_id' => $oferta->customer_id,
                'fecha' => $datos['fecha'] ?? now()->toDateString(),
                'subtotal_servicios' => $totales[FacturaLinea::TIPO_SERVICIO],
                'subtotal_gastos' => $totales[FacturaLinea::TIPO_GASTO],
                'subtotal_productos' => $totales[FacturaLinea::TIPO_PRODUCTO],
                'total' => round(array_sum($totales), 2),
                'nota' => $datos['nota'] ?? $oferta->nota,
                'forma_pago' => $datos['forma_pago'] ?? $oferta->forma_pago,
                'observaciones' => $datos['observaciones'] ?? $oferta->observaciones,
                'firma_pie' => $datos['firma_pie'] ?? $oferta->firma_pie,
                'almacen_id' => $almacenId,
                'user_id' => $usuario?->id,
            ]);

            $factura->lineas()->createMany($lineas);

            $this->descontarStock($factura, $oferta, $almacenId, $usuario);

            $oferta->update([
                'estado' => Oferta::ESTADO_FACTURADO,
                'facturada_at' => now(),
                'almacen_id' => $almacenId,
                'nota' => $factura->nota,
                'forma_pago' => $factura->forma_pago,
                'observaciones' => $factura->observaciones,
                'firma_pie' => $factura->firma_pie,
            ]);

            return $factura->refresh()->load([
                'oferta',
                'customer',
                'almacen',
                'user',
                'lineas',
            ]);
        });
    }

    /**
     * @throws ValidationException
     */
    private function validarEstado(Oferta $oferta): void
    {
        if ($oferta->estaFacturada() || $oferta->factura) {
            throw ValidationException::withMessages([
                'oferta' => 'La oferta ya fue facturada.',
            ]);
        }
    }

    /**
     * Evitar facturas incompletas si el formulario omite líneas.
     *
     * @param  array<string, mixed>  $datos
     *
     * @throws ValidationException
     */
    private function validarLineasCompletas(Oferta $oferta, array $datos): void
    {
        $idsOferta = $oferta->servicios->pluck('id')->map(fn ($id): int => (int) $id)->sort()->values();
        $idsEnviados = collect($datos['servicios'] ?? [])->pluck('id')->map(fn ($id): int => (int) $id)->sort()->values();

        if ($idsOferta->all() !== $idsEnviados->all()) {
            throw ValidationException::withMessages([
                'servicios' => 'Debe confirmar la cantidad y el precio de todas las líneas de servicio.',
            ]);
        }

        $idsOferta = $oferta->gastos->pluck('id')->map(fn ($id): int => (int) $id)->sort()->values();
        $idsEnviados = collect($datos['gastos'] ?? [])->pluck('id')->map(fn ($id): int => (int) $id)->sort()->values();

        if ($idsOferta->all() !== $idsEnviados->all()) {
            throw ValidationException::withMessages([
                'gastos' => 'Debe confirmar la cantidad y el precio de todas las líneas de gasto.',
            ]);
        }

        $idsOferta = $oferta->servicios
            ->flatMap(fn (OfertaServicio $linea) => $linea->productos)
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->sort()
            ->values();

        $idsEnviados = collect($datos['productos'] ?? [])->pluck('id')->map(fn ($id): int => (int) $id)->sort()->values();

        if ($idsOferta->all() !== $idsEnviados->all()) {
            throw ValidationException::withMessages([
                'productos' => 'Debe confirmar la cantidad real de todos los productos utilizados.',
            ]);
        }
    }

    /**
     * Construir el snapshot de líneas que quedará en la factura.
     *
     * @return array<int, array<string, mixed>>
     */
    private function construirLineas(Oferta $oferta): array
    {
        $lineas = [];
        $orden = 0;

        foreach ($oferta->servicios as $linea) {
            $lineas[] = [
                'tipo' => FacturaLinea::TIPO_SERVICIO,
                'descripcion' => Str::limit($linea->servicio?->nombre ?? 'Servicio eliminado', 200, ''),
                'cantidad' => round((float) $linea->cantidad, 2),
                'precio_unitario' => round((float) $linea->precio_unitario, 2),
                'subtotal' => round((float) $linea->total, 2),
                'servicio_id' => $linea->servicio_id,
                'gasto_id' => null,
                'producto_id' => null,
                'orden' => $orden++,
            ];

            foreach ($linea->productos as $producto) {
                $lineas[] = [
                    'tipo' => FacturaLinea::TIPO_PRODUCTO,
                    'descripcion' => Str::limit(sprintf(
                        '%s (servicio: %s)',
                        $producto->producto?->nombre ?? 'Producto eliminado',
                        $linea->servicio?->nombre ?? 'servicio eliminado',
                    ), 200, ''),
                    'cantidad' => (float) $producto->cantidad,
                    'precio_unitario' => round((float) $producto->precio_venta, 2),
                    'subtotal' => round((float) $producto->total, 2),
                    'servicio_id' => null,
                    'gasto_id' => null,
                    'producto_id' => $producto->producto_id,
                    'orden' => $orden++,
                ];
            }
        }

        foreach ($oferta->gastos as $linea) {
            $lineas[] = [
                'tipo' => FacturaLinea::TIPO_GASTO,
                'descripcion' => Str::limit($linea->gasto?->nombre ?? 'Gasto eliminado', 200, ''),
                'cantidad' => round((float) $linea->cantidad, 2),
                'precio_unitario' => round((float) $linea->precio_unitario, 2),
                'subtotal' => round((float) $linea->total, 2),
                'servicio_id' => null,
                'gasto_id' => $linea->gasto_id,
                'producto_id' => null,
                'orden' => $orden++,
            ];
        }

        return $lineas;
    }

    /**
     * @param  array<int, array<string, mixed>>  $lineas
     * @return array<string, float>
     */
    private function totalesPorTipo(array $lineas): array
    {
        $totales = [
            FacturaLinea::TIPO_SERVICIO => 0.0,
            FacturaLinea::TIPO_GASTO => 0.0,
            FacturaLinea::TIPO_PRODUCTO => 0.0,
        ];

        foreach ($lineas as $linea) {
            $totales[$linea['tipo']] += (float) $linea['subtotal'];
        }

        return array_map(fn (float $total): float => round($total, 2), $totales);
    }

    /**
     * Descontar del almacén las cantidades reales confirmadas.
     */
    private function descontarStock(
        Factura $factura,
        Oferta $oferta,
        int $almacenId,
        ?User $usuario,
    ): void {
        $productos = $oferta->servicios
            ->flatMap(fn (OfertaServicio $linea) => $linea->productos)
            ->groupBy('producto_id')
            ->map(fn ($lineas, $productoId): array => [
                'producto_id' => (int) $productoId,
                'cantidad' => (int) $lineas->sum('cantidad'),
            ])
            ->values()
            ->all();

        if ($productos === []) {
            return;
        }

        $operacion = $this->inventario->registrarSalida([
            'almacen_id' => $almacenId,
            'referencia' => Str::limit(
                "Factura {$factura->codigo} / Oferta {$oferta->codigo}",
                60,
                '',
            ),
            'notas' => 'Descuento automático por facturación de la oferta.',
            'lineas' => $productos,
        ], $usuario);

        $operacion->update(['factura_id' => $factura->id]);
    }
}
