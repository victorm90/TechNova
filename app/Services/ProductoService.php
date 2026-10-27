<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProductoService
{
    public function __construct(
        private readonly SecuenciaService $secuencias,
        private readonly InventarioService $inventario,
    ) {}

    /**
     * Crear un producto con SKU consecutivo y su inventario inicial.
     *
     * @param  array<string, mixed>  $datos
     */
    public function crear(array $datos, ?User $usuario = null): Producto
    {
        return DB::transaction(function () use ($datos, $usuario): Producto {
            $producto = Producto::create([
                'sku' => $this->secuencias->siguiente('productos', 'PROD-'),
                'nombre' => $datos['nombre'],
                'descripcion' => $datos['descripcion'] ?? null,
                'categoria_producto_id' => $datos['categoria_producto_id'],
                'unidad_medida_id' => $datos['unidad_medida_id'],
                'precio_compra' => $datos['precio_compra'] ?? 0,
                'precio_venta' => $datos['precio_venta'] ?? 0,
                'stock_minimo' => $datos['stock_minimo'] ?? 0,
                'activo' => $datos['activo'] ?? true,
            ]);

            if (! empty($datos['almacen_id'])) {
                $cantidadInicial = round((float) ($datos['cantidad_inicial'] ?? 0), 2);

                if ($cantidadInicial > 0) {
                    $this->inventario->registrarEntrada([
                        'almacen_id' => (int) $datos['almacen_id'],
                        'referencia' => 'Inventario inicial',
                        'notas' => "Carga inicial al crear el producto {$producto->sku}.",
                        'lineas' => [[
                            'producto_id' => $producto->id,
                            'cantidad' => $cantidadInicial,
                            'costo_unitario' => $datos['precio_compra'] ?? 0,
                        ]],
                    ], $usuario);
                } else {
                    $this->inventario->asegurarRegistro($producto->id, (int) $datos['almacen_id']);
                }
            }

            return $producto->refresh();
        });
    }

    /**
     * Actualizar los datos maestros de un producto sin alterar existencias.
     *
     * @param  array<string, mixed>  $datos
     */
    public function actualizar(Producto $producto, array $datos): Producto
    {
        return DB::transaction(function () use ($producto, $datos): Producto {
            $producto->update([
                'nombre' => $datos['nombre'],
                'descripcion' => $datos['descripcion'] ?? null,
                'categoria_producto_id' => $datos['categoria_producto_id'],
                'unidad_medida_id' => $datos['unidad_medida_id'],
                'precio_compra' => $datos['precio_compra'] ?? 0,
                'precio_venta' => $datos['precio_venta'] ?? 0,
                'stock_minimo' => $datos['stock_minimo'] ?? 0,
                'activo' => $datos['activo'] ?? true,
            ]);

            return $producto->refresh();
        });
    }
}
