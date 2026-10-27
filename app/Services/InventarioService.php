<?php

namespace App\Services;

use App\Models\Inventario;
use App\Models\MovimientoInventario;
use App\Models\OperacionInventario;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventarioService
{
    public function __construct(private readonly SecuenciaService $secuencias) {}

    /**
     * Registrar una entrada de inventario con una o varias líneas.
     *
     * @param  array{
     *     almacen_id: int,
     *     proveedor_id?: int|null,
     *     referencia?: string|null,
     *     notas?: string|null,
     *     lineas: array<int, array{producto_id: int, cantidad: numeric, costo_unitario?: numeric}>
     * }  $datos
     */
    public function registrarEntrada(array $datos, ?User $usuario = null): OperacionInventario
    {
        return DB::transaction(function () use ($datos, $usuario): OperacionInventario {
            $lineas = $this->normalizarLineas($datos['lineas'] ?? []);

            $operacion = OperacionInventario::create([
                'codigo' => $this->secuencias->siguiente('operaciones_entrada', 'ENT-'),
                'tipo' => OperacionInventario::TIPO_ENTRADA,
                'almacen_id' => (int) $datos['almacen_id'],
                'proveedor_id' => $datos['proveedor_id'] ?? null,
                'user_id' => $usuario?->id,
                'referencia' => $datos['referencia'] ?? null,
                'notas' => $datos['notas'] ?? null,
                'total' => 0,
            ]);

            $totalOperacion = 0.0;

            foreach ($lineas as $indice => $linea) {
                $producto = $this->productoDisponible($linea['producto_id'], $indice);
                $cantidad = round((float) $linea['cantidad'], 2);
                $costoUnitario = round((float) ($linea['costo_unitario'] ?? 0), 2);

                $inventario = $this->bloquearInventario($producto->id, (int) $datos['almacen_id']);

                $cantidadAnterior = (float) $inventario->cantidad;
                $costoAnterior = (float) $inventario->costo_promedio;
                $nuevaCantidad = round($cantidadAnterior + $cantidad, 2);

                $nuevoCosto = $nuevaCantidad > 0
                    ? round((($cantidadAnterior * $costoAnterior) + ($cantidad * $costoUnitario)) / $nuevaCantidad, 4)
                    : $costoUnitario;

                $inventario->update([
                    'cantidad' => $nuevaCantidad,
                    'costo_promedio' => $nuevoCosto,
                    'ultimo_movimiento_at' => now(),
                ]);

                if ($costoUnitario > 0) {
                    $producto->update(['precio_compra' => $costoUnitario]);
                }

                $subtotal = round($cantidad * $costoUnitario, 2);

                $operacion->lineas()->create([
                    'producto_id' => $producto->id,
                    'cantidad' => $cantidad,
                    'costo_unitario' => $costoUnitario,
                    'subtotal' => $subtotal,
                ]);

                $this->registrarMovimiento(
                    operacion: $operacion,
                    producto: $producto,
                    inventario: $inventario,
                    tipo: OperacionInventario::TIPO_ENTRADA,
                    cantidad: $cantidad,
                    saldo: $nuevaCantidad,
                    costoUnitario: $costoUnitario,
                    usuario: $usuario,
                );

                $totalOperacion += $subtotal;
            }

            $operacion->update(['total' => round($totalOperacion, 2)]);

            return $operacion->refresh();
        });
    }

    /**
     * Registrar una salida de inventario con una o varias líneas.
     *
     * @param  array{
     *     almacen_id: int,
     *     referencia?: string|null,
     *     notas?: string|null,
     *     lineas: array<int, array{producto_id: int, cantidad: numeric}>
     * }  $datos
     */
    public function registrarSalida(array $datos, ?User $usuario = null): OperacionInventario
    {
        return DB::transaction(function () use ($datos, $usuario): OperacionInventario {
            $lineas = $this->normalizarLineas($datos['lineas'] ?? []);

            $operacion = OperacionInventario::create([
                'codigo' => $this->secuencias->siguiente('operaciones_salida', 'SAL-'),
                'tipo' => OperacionInventario::TIPO_SALIDA,
                'almacen_id' => (int) $datos['almacen_id'],
                'proveedor_id' => null,
                'user_id' => $usuario?->id,
                'referencia' => $datos['referencia'] ?? null,
                'notas' => $datos['notas'] ?? null,
                'total' => 0,
            ]);

            $totalOperacion = 0.0;

            foreach ($lineas as $indice => $linea) {
                $producto = $this->productoDisponible($linea['producto_id'], $indice);
                $cantidad = round((float) $linea['cantidad'], 2);

                $inventario = Inventario::query()
                    ->where('producto_id', $producto->id)
                    ->where('almacen_id', (int) $datos['almacen_id'])
                    ->lockForUpdate()
                    ->first();

                $disponible = $inventario ? (float) $inventario->cantidad : 0.0;

                if (! $inventario || $cantidad > $disponible) {
                    throw ValidationException::withMessages([
                        "lineas.{$indice}.cantidad" => sprintf(
                            'Stock insuficiente para "%s". Disponible: %s %s.',
                            $producto->nombre,
                            number_format($disponible, 2, ',', '.'),
                            $producto->unidadMedida?->abreviatura ?? 'unidades',
                        ),
                    ]);
                }

                $costoUnitario = round((float) $inventario->costo_promedio, 2);
                $nuevaCantidad = round($disponible - $cantidad, 2);

                $inventario->update([
                    'cantidad' => $nuevaCantidad,
                    'ultimo_movimiento_at' => now(),
                ]);

                $subtotal = round($cantidad * $costoUnitario, 2);

                $operacion->lineas()->create([
                    'producto_id' => $producto->id,
                    'cantidad' => $cantidad,
                    'costo_unitario' => $costoUnitario,
                    'subtotal' => $subtotal,
                ]);

                $this->registrarMovimiento(
                    operacion: $operacion,
                    producto: $producto,
                    inventario: $inventario,
                    tipo: OperacionInventario::TIPO_SALIDA,
                    cantidad: $cantidad,
                    saldo: $nuevaCantidad,
                    costoUnitario: $costoUnitario,
                    usuario: $usuario,
                );

                $totalOperacion += $subtotal;
            }

            $operacion->update(['total' => round($totalOperacion, 2)]);

            return $operacion->refresh();
        });
    }

    /**
     * Crear el registro de inventario de un producto en un almacén si no existe.
     */
    public function asegurarRegistro(int $productoId, int $almacenId): Inventario
    {
        return Inventario::query()->firstOrCreate(
            ['producto_id' => $productoId, 'almacen_id' => $almacenId],
            ['cantidad' => 0, 'costo_promedio' => 0],
        );
    }

    /**
     * Obtener y bloquear la fila de inventario del producto en el almacén.
     */
    private function bloquearInventario(int $productoId, int $almacenId): Inventario
    {
        $inventario = Inventario::query()
            ->where('producto_id', $productoId)
            ->where('almacen_id', $almacenId)
            ->lockForUpdate()
            ->first();

        if ($inventario) {
            return $inventario;
        }

        try {
            Inventario::create([
                'producto_id' => $productoId,
                'almacen_id' => $almacenId,
                'cantidad' => 0,
                'costo_promedio' => 0,
            ]);
        } catch (QueryException) {
            // Otra transacción creó la fila; se recupera con bloqueo a continuación.
        }

        return Inventario::query()
            ->where('producto_id', $productoId)
            ->where('almacen_id', $almacenId)
            ->lockForUpdate()
            ->firstOrFail();
    }

    /**
     * Validar y normalizar las líneas del documento.
     *
     * @param  array<int, array<string, mixed>>  $lineas
     * @return array<int, array{producto_id: int, cantidad: float, costo_unitario?: float}>
     */
    private function normalizarLineas(array $lineas): array
    {
        if ($lineas === []) {
            throw ValidationException::withMessages([
                'lineas' => 'Debes agregar al menos un producto a la operación.',
            ]);
        }

        $productosVistos = [];
        $normalizadas = [];

        foreach (array_values($lineas) as $indice => $linea) {
            $productoId = (int) ($linea['producto_id'] ?? 0);
            $cantidad = round((float) ($linea['cantidad'] ?? 0), 2);

            if ($productoId <= 0) {
                throw ValidationException::withMessages([
                    "lineas.{$indice}.producto_id" => 'Selecciona un producto válido.',
                ]);
            }

            if ($cantidad <= 0) {
                throw ValidationException::withMessages([
                    "lineas.{$indice}.cantidad" => 'La cantidad debe ser mayor que cero.',
                ]);
            }

            if (isset($productosVistos[$productoId])) {
                throw ValidationException::withMessages([
                    "lineas.{$indice}.producto_id" => 'No puedes repetir el mismo producto en una operación.',
                ]);
            }

            $productosVistos[$productoId] = true;

            $normalizadas[] = [
                'producto_id' => $productoId,
                'cantidad' => $cantidad,
                'costo_unitario' => round((float) ($linea['costo_unitario'] ?? 0), 2),
            ];
        }

        return $normalizadas;
    }

    /**
     * Obtener el producto y validar que esté activo.
     */
    private function productoDisponible(int $productoId, int $indice): Producto
    {
        $producto = Producto::query()->with('unidadMedida')->find($productoId);

        if (! $producto) {
            throw ValidationException::withMessages([
                "lineas.{$indice}.producto_id" => 'El producto seleccionado no existe.',
            ]);
        }

        if (! $producto->activo) {
            throw ValidationException::withMessages([
                "lineas.{$indice}.producto_id" => "El producto \"{$producto->nombre}\" está inactivo.",
            ]);
        }

        return $producto;
    }

    /**
     * Guardar el movimiento auditable de la operación.
     */
    private function registrarMovimiento(
        OperacionInventario $operacion,
        Producto $producto,
        Inventario $inventario,
        string $tipo,
        float $cantidad,
        float $saldo,
        float $costoUnitario,
        ?User $usuario,
    ): void {
        MovimientoInventario::create([
            'operacion_inventario_id' => $operacion->id,
            'producto_id' => $producto->id,
            'almacen_id' => $inventario->almacen_id,
            'tipo' => $tipo,
            'cantidad' => $cantidad,
            'saldo_resultante' => $saldo,
            'costo_unitario' => $costoUnitario,
            'user_id' => $usuario?->id,
        ]);
    }
}
