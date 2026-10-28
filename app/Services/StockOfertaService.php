<?php

namespace App\Services;

use App\Models\Almacen;
use App\Models\Inventario;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class StockOfertaService
{
    /**
     * Almacén activo predeterminado para consumir productos.
     */
    public function almacenPredeterminadoId(): ?int
    {
        return Almacen::query()
            ->where('activo', true)
            ->orderBy('id')
            ->value('id');
    }

    /**
     * Existencias disponibles por producto en un almacén.
     *
     * @return Collection<int, int>
     */
    public function existencias(int $almacenId): Collection
    {
        return Inventario::query()
            ->where('almacen_id', $almacenId)
            ->pluck('cantidad', 'producto_id')
            ->map(fn (mixed $cantidad): int => (int) $cantidad);
    }

    /**
     * Validar que las cantidades solicitadas no superen el stock disponible.
     *
     * @param  array<int, array{producto_id: int, cantidad: int}>  $requerimientos
     *
     * @throws ValidationException
     */
    public function validar(array $requerimientos, ?int $almacenId): void
    {
        $requerimientos = array_values(array_filter(
            $requerimientos,
            fn (array $linea): bool => (int) ($linea['producto_id'] ?? 0) > 0
                && (int) ($linea['cantidad'] ?? 0) > 0,
        ));

        if ($requerimientos === []) {
            return;
        }

        if ($almacenId === null) {
            throw ValidationException::withMessages([
                'almacen_id' => 'Seleccione un almacén para consumir los productos de la oferta.',
            ]);
        }

        $totales = [];

        foreach ($requerimientos as $linea) {
            $productoId = (int) $linea['producto_id'];
            $totales[$productoId] = ($totales[$productoId] ?? 0) + (int) $linea['cantidad'];
        }

        $inventarios = Inventario::query()
            ->with(['producto.unidadMedida'])
            ->where('almacen_id', $almacenId)
            ->whereIn('producto_id', array_keys($totales))
            ->get()
            ->keyBy('producto_id');

        $errores = [];

        foreach ($totales as $productoId => $solicitado) {
            $inventario = $inventarios->get($productoId);
            $disponible = (int) ($inventario?->cantidad ?? 0);

            if ($solicitado <= $disponible) {
                continue;
            }

            $producto = $inventario?->producto;
            $unidad = $producto?->unidadMedida?->abreviatura
                ?: ($producto?->unidadMedida?->nombre ?? 'unidades');

            $errores['stock'] = sprintf(
                'Stock insuficiente para "%s". Disponible: %s %s; solicitado: %s %s.',
                $producto?->nombre ?? "producto #{$productoId}",
                number_format($disponible, 0, ',', '.'),
                $unidad,
                number_format($solicitado, 0, ',', '.'),
                $unidad,
            );

            break;
        }

        if ($errores !== []) {
            throw ValidationException::withMessages($errores);
        }
    }
}
