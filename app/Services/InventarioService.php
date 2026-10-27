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
     *     lineas: array<int, array{producto_id: int, cantidad: int, precio_compra: numeric, stock_minimo: int}>
     * }  $datos
     */
    public function registrarEntrada(array $datos, ?User $usuario = null): OperacionInventario
    {
        return DB::transaction(function () use ($datos, $usuario): OperacionInventario {
            $lineas = $this->normalizarLineas($datos['lineas'] ?? [], true);

            $operacion = OperacionInventario::create([
                'codigo' => $this->secuencias->siguiente('operaciones_entrada', 'ENT-'),
                'tipo' => OperacionInventario::TIPO_ENTRADA,
                'almacen_id' => (int) $datos['almacen_id'],
                'proveedor_id' => $datos['proveedor_id'] ?? null,
                'user_id' => $usuario?->id,
                'referencia' => null,
                'notas' => null,
                'total' => 0,
            ]);

            $totalOperacion = 0.0;

            foreach ($lineas as $indice => $linea) {
                $producto = $this->productoDisponible($linea['producto_id'], $indice);
                $cantidad = (int) $linea['cantidad'];
                $costoUnitario = round((float) $linea['precio_compra'], 2);
                $stockMinimo = (int) $linea['stock_minimo'];

                $inventario = $this->bloquearInventario($producto->id, (int) $datos['almacen_id']);

                $cantidadAnterior = (int) $inventario->cantidad;
                $costoAnterior = (float) $inventario->costo_promedio;
                $nuevaCantidad = $cantidadAnterior + $cantidad;

                $nuevoCosto = $nuevaCantidad > 0
                    ? round((($cantidadAnterior * $costoAnterior) + ($cantidad * $costoUnitario)) / $nuevaCantidad, 4)
                    : $costoUnitario;

                $inventario->update([
                    'cantidad' => $nuevaCantidad,
                    'costo_promedio' => $nuevoCosto,
                    'ultimo_movimiento_at' => now(),
                ]);

                $producto->update([
                    'precio_compra' => $costoUnitario,
                    'stock_minimo' => $stockMinimo,
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
     *     lineas: array<int, array{producto_id: int, cantidad: int}>
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
                $cantidad = (int) $linea['cantidad'];

                $inventario = Inventario::query()
                    ->where('producto_id', $producto->id)
                    ->where('almacen_id', (int) $datos['almacen_id'])
                    ->lockForUpdate()
                    ->first();

                $disponible = $inventario ? (int) $inventario->cantidad : 0;

                if (! $inventario || $cantidad > $disponible) {
                    throw ValidationException::withMessages([
                        "lineas.{$indice}.cantidad" => sprintf(
                            'Stock insuficiente para "%s". Disponible: %s %s.',
                            $producto->nombre,
                            number_format($disponible, 0, ',', '.'),
                            $producto->unidadMedida?->abreviatura ?? 'unidades',
                        ),
                    ]);
                }

                $costoUnitario = round((float) $inventario->costo_promedio, 2);
                $nuevaCantidad = $disponible - $cantidad;

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
     * Ajustar la cantidad en stock de un producto ya existente en un almacén.
     * No crea productos ni modifica precios.
     *
     * @param  array{inventario_id: int, cantidad: int}  $datos
     */
    public function registrarAjuste(array $datos, ?User $usuario = null): OperacionInventario
    {
        return DB::transaction(function () use ($datos, $usuario): OperacionInventario {
            $inventario = Inventario::query()
                ->with('producto')
                ->lockForUpdate()
                ->find($datos['inventario_id']);

            if (! $inventario || ! $inventario->producto) {
                throw ValidationException::withMessages([
                    'inventario_id' => 'El producto no existe en el almacén seleccionado.',
                ]);
            }

            $cantidadAnterior = (int) $inventario->cantidad;
            $cantidadNueva = (int) $datos['cantidad'];

            if ($cantidadNueva < 0) {
                throw ValidationException::withMessages([
                    'cantidad' => 'La cantidad en stock no puede ser negativa.',
                ]);
            }

            if ($cantidadNueva === $cantidadAnterior) {
                throw ValidationException::withMessages([
                    'cantidad' => 'La cantidad indicada es igual al stock actual. No hay nada que ajustar.',
                ]);
            }

            $diferencia = $cantidadNueva - $cantidadAnterior;
            $costoUnitario = round((float) $inventario->costo_promedio, 4);

            $inventario->update([
                'cantidad' => $cantidadNueva,
                'ultimo_movimiento_at' => now(),
            ]);

            $operacion = OperacionInventario::create([
                'codigo' => $this->secuencias->siguiente('operaciones_ajuste', 'AJU-'),
                'tipo' => OperacionInventario::TIPO_AJUSTE,
                'almacen_id' => $inventario->almacen_id,
                'proveedor_id' => null,
                'user_id' => $usuario?->id,
                'referencia' => 'Ajuste de inventario',
                'notas' => sprintf(
                    'Stock ajustado de %s a %s unidades.',
                    number_format($cantidadAnterior, 0, ',', '.'),
                    number_format($cantidadNueva, 0, ',', '.'),
                ),
                'total' => 0,
            ]);

            $operacion->lineas()->create([
                'producto_id' => $inventario->producto_id,
                'cantidad' => $cantidadNueva,
                'costo_unitario' => $costoUnitario,
                'subtotal' => 0,
            ]);

            $this->registrarMovimiento(
                operacion: $operacion,
                producto: $inventario->producto,
                inventario: $inventario,
                tipo: OperacionInventario::TIPO_AJUSTE,
                cantidad: $diferencia,
                saldo: $cantidadNueva,
                costoUnitario: $costoUnitario,
                usuario: $usuario,
            );

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
     * @return array<int, array{producto_id: int, cantidad: int, precio_compra?: float, stock_minimo?: int}>
     */
    private function normalizarLineas(array $lineas, bool $conPrecioYMinimo = false): array
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
            $cantidad = filter_var($linea['cantidad'] ?? null, FILTER_VALIDATE_INT);

            if ($productoId <= 0) {
                throw ValidationException::withMessages([
                    "lineas.{$indice}.producto_id" => 'Selecciona un producto válido.',
                ]);
            }

            if ($cantidad === false || $cantidad <= 0) {
                throw ValidationException::withMessages([
                    "lineas.{$indice}.cantidad" => 'La cantidad debe ser un número entero mayor que cero.',
                ]);
            }

            if (isset($productosVistos[$productoId])) {
                throw ValidationException::withMessages([
                    "lineas.{$indice}.producto_id" => 'No puedes repetir el mismo producto en una operación.',
                ]);
            }

            $productosVistos[$productoId] = true;

            $normalizada = [
                'producto_id' => $productoId,
                'cantidad' => $cantidad,
            ];

            if ($conPrecioYMinimo) {
                $precio = round((float) ($linea['precio_compra'] ?? 0), 2);
                $stockMinimo = (int) ($linea['stock_minimo'] ?? 0);

                if ($precio < 0) {
                    throw ValidationException::withMessages([
                        "lineas.{$indice}.precio_compra" => 'El precio de compra no puede ser negativo.',
                    ]);
                }

                if ($stockMinimo < 0) {
                    throw ValidationException::withMessages([
                        "lineas.{$indice}.stock_minimo" => 'El stock mínimo no puede ser negativo.',
                    ]);
                }

                $normalizada['precio_compra'] = $precio;
                $normalizada['stock_minimo'] = $stockMinimo;
            }

            $normalizadas[] = $normalizada;
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
        int $cantidad,
        int $saldo,
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
