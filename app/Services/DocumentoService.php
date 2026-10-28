<?php

namespace App\Services;

use App\Models\Factura;
use App\Models\FacturaLinea;
use App\Models\Oferta;
use App\Models\OfertaServicio;
use App\Models\OfertaServicioProducto;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class DocumentoService
{
    /**
     * Datos normalizados de una oferta para impresión y PDF.
     *
     * @return array<string, mixed>
     */
    public function oferta(Oferta $oferta): array
    {
        $oferta->loadMissing([
            'customer',
            'servicios.servicio.unidadMedida',
            'servicios.productos.producto.unidadMedida',
            'gastos.gasto',
        ]);

        $servicios = $oferta->servicios->values()->map(
            fn (OfertaServicio $linea, int $indice): array => [
                'numero' => $indice + 1,
                'descripcion' => $linea->servicio?->nombre ?? 'Servicio eliminado',
                'unidad' => $this->unidad($linea->servicio?->unidadMedida?->abreviatura, $linea->servicio?->unidadMedida?->nombre),
                'cantidad' => (float) $linea->cantidad,
                'precio' => (float) $linea->precio_unitario,
                'subtotal' => (float) $linea->total,
            ],
        );

        $productos = $oferta->servicios
            ->flatMap(fn (OfertaServicio $linea) => $linea->productos->map(
                fn (OfertaServicioProducto $producto): array => [
                    'descripcion' => $producto->producto?->nombre ?? 'Producto eliminado',
                    'servicio' => $linea->servicio?->nombre ?? 'Servicio eliminado',
                    'unidad' => $this->unidad($producto->producto?->unidadMedida?->abreviatura, $producto->producto?->unidadMedida?->nombre),
                    'cantidad' => (float) $producto->cantidad,
                    'precio' => (float) $producto->precio_venta,
                    'subtotal' => (float) $producto->total,
                ],
            ))
            ->values();

        $gastos = $oferta->gastos->values()->map(
            fn ($linea, int $indice): array => [
                'numero' => $indice + 1,
                'descripcion' => $linea->gasto?->nombre ?? 'Gasto eliminado',
                'cantidad' => (float) $linea->cantidad,
                'precio' => (float) $linea->precio_unitario,
                'subtotal' => (float) $linea->total,
            ],
        );

        return $this->documento(
            tipo: 'OFERTA',
            numero: (string) $oferta->codigo,
            fecha: $oferta->fecha,
            oferta: $oferta,
            servicios: $servicios->all(),
            productos: $productos->all(),
            gastos: $gastos->all(),
            subtotales: [
                'servicios' => (float) $oferta->total_servicios,
                'productos' => (float) $oferta->total_productos,
                'gastos' => (float) $oferta->total_gastos,
                'total' => (float) $oferta->total,
            ],
            nota: $oferta->nota,
            formaPago: $oferta->forma_pago,
            observaciones: $oferta->observaciones,
            firma: $oferta->firma_pie ?: config('empresa.firma_pie'),
            vigencia: (int) ($oferta->vigencia_dias ?: config('empresa.vigencia_dias')),
        );
    }

    /**
     * Datos normalizados de la factura (snapshot inmutable).
     *
     * @return array<string, mixed>
     */
    public function factura(Factura $factura): array
    {
        $factura->loadMissing(['customer', 'oferta', 'lineas']);

        $lineas = $factura->lineas;

        $servicios = $lineas
            ->where('tipo', FacturaLinea::TIPO_SERVICIO)
            ->values()
            ->map(fn (FacturaLinea $linea, int $indice): array => [
                'numero' => $indice + 1,
                'descripcion' => $linea->descripcion,
                'unidad' => 'ud',
                'cantidad' => (float) $linea->cantidad,
                'precio' => (float) $linea->precio_unitario,
                'subtotal' => (float) $linea->subtotal,
            ]);

        $productos = $this->lineasFactura($lineas, FacturaLinea::TIPO_PRODUCTO)
            ->map(fn (FacturaLinea $linea): array => [
                'descripcion' => $linea->descripcion,
                'servicio' => '',
                'unidad' => 'ud',
                'cantidad' => (float) $linea->cantidad,
                'precio' => (float) $linea->precio_unitario,
                'subtotal' => (float) $linea->subtotal,
            ]);

        $gastos = $this->lineasFactura($lineas, FacturaLinea::TIPO_GASTO)
            ->values()
            ->map(fn (FacturaLinea $linea, int $indice): array => [
                'numero' => $indice + 1,
                'descripcion' => $linea->descripcion,
                'cantidad' => (float) $linea->cantidad,
                'precio' => (float) $linea->precio_unitario,
                'subtotal' => (float) $linea->subtotal,
            ]);

        return $this->documento(
            tipo: 'FACTURA',
            numero: (string) $factura->codigo,
            fecha: $factura->fecha,
            oferta: $factura->oferta,
            servicios: $servicios->all(),
            productos: $productos->all(),
            gastos: $gastos->all(),
            subtotales: [
                'servicios' => (float) $factura->subtotal_servicios,
                'productos' => (float) $factura->subtotal_productos,
                'gastos' => (float) $factura->subtotal_gastos,
                'total' => (float) $factura->total,
            ],
            nota: $factura->nota,
            formaPago: $factura->forma_pago,
            observaciones: $factura->observaciones,
            firma: $factura->firma_pie ?: config('empresa.firma_pie'),
            vigencia: (int) config('empresa.vigencia_dias'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function documento(
        string $tipo,
        string $numero,
        mixed $fecha,
        ?Oferta $oferta,
        array $servicios,
        array $productos,
        array $gastos,
        array $subtotales,
        ?string $nota,
        ?string $formaPago,
        ?string $observaciones,
        ?string $firma,
        int $vigencia,
    ): array {
        return [
            'tipo' => $tipo,
            'numero' => $numero,
            'fecha' => $fecha,
            'oferta_codigo' => $oferta?->codigo,
            'cliente' => [
                'nombre' => $oferta?->customer?->name ?? 'Cliente no disponible',
                'direccion' => $oferta?->customer?->address,
                'telefono' => $oferta?->customer?->phone,
                'identificacion' => $oferta?->customer?->identity_card,
            ],
            'empresa' => $this->empresa(),
            'logo' => $this->logoDataUri(),
            'servicios' => $servicios,
            'productos' => $productos,
            'gastos' => $gastos,
            'subtotales' => $subtotales,
            'nota' => $nota,
            'forma_pago' => $formaPago,
            'observaciones' => $observaciones,
            'firma' => $firma,
            'vigencia' => $vigencia,
            'moneda' => config('empresa.moneda', 'MN'),
        ];
    }

    /**
     * @return array<string, string|null>
     */
    public function empresa(): array
    {
        return [
            'nombre' => (string) config('empresa.nombre'),
            'direccion' => (string) config('empresa.direccion'),
            'telefono' => (string) config('empresa.telefono'),
            'correo' => (string) config('empresa.correo'),
            'identificacion_fiscal' => (string) config('empresa.identificacion_fiscal'),
            'sitio_web' => (string) config('empresa.sitio_web'),
        ];
    }

    /**
     * Logotipo embebido en base64 (seguro para dompdf y para impresión).
     */
    public function logoDataUri(): ?string
    {
        $ruta = config('empresa.logo');

        if (! is_string($ruta) || $ruta === '') {
            return null;
        }

        $absoluta = preg_match('/^[A-Za-z]:[\\\\\\/]/', $ruta) === 1
            ? $ruta
            : public_path(ltrim($ruta, '/\\'));

        if (! File::isFile($absoluta)) {
            return null;
        }

        $mime = File::mimeType($absoluta) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode(File::get($absoluta));
    }

    /**
     * @param  Collection<int, FacturaLinea>  $lineas
     * @return Collection<int, FacturaLinea>
     */
    private function lineasFactura(Collection $lineas, string $tipo): Collection
    {
        return $lineas->where('tipo', $tipo)->values();
    }

    private function unidad(?string $abreviatura, ?string $nombre): string
    {
        return $abreviatura ?: ($nombre ?: 'ud');
    }
}
