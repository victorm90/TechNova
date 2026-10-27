<?php

namespace Database\Seeders;

use App\Models\Almacen;
use App\Models\CategoriaProducto;
use App\Models\Producto;
use App\Models\UnidadMedida;
use App\Models\User;
use App\Services\InventarioService;
use App\Services\ProductoService;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $almacen = Almacen::query()->where('nombre', 'Almacén principal')->first();

        if (! $almacen) {
            return;
        }

        /** @var ProductoService $productos */
        $productos = app(ProductoService::class);

        /** @var InventarioService $inventario */
        $inventario = app(InventarioService::class);

        $admin = User::query()->where('role', User::ROLE_ADMIN)->first();

        foreach ($this->productos() as $datos) {
            $categoria = CategoriaProducto::query()->where('nombre', $datos['categoria'])->first();
            $unidad = UnidadMedida::query()->where('nombre', $datos['unidad'])->first();

            if (! $categoria || ! $unidad) {
                continue;
            }

            $existente = Producto::query()
                ->where('nombre', $datos['nombre'])
                ->where('categoria_producto_id', $categoria->id)
                ->exists();

            if ($existente) {
                continue;
            }

            $producto = $productos->crear([
                'nombre' => $datos['nombre'],
                'categoria_producto_id' => $categoria->id,
                'unidad_medida_id' => $unidad->id,
            ]);

            $producto->update([
                'descripcion' => $datos['descripcion'],
                'precio_venta' => $datos['precio_venta'],
            ]);

            $cantidadInicial = (int) $datos['cantidad_inicial'];

            if ($cantidadInicial > 0) {
                $inventario->registrarEntrada([
                    'almacen_id' => $almacen->id,
                    'lineas' => [[
                        'producto_id' => $producto->id,
                        'cantidad' => $cantidadInicial,
                        'precio_compra' => $datos['precio_compra'],
                        'stock_minimo' => (int) $datos['stock_minimo'],
                    ]],
                ], $admin);
            } else {
                $inventario->asegurarRegistro($producto->id, $almacen->id);
            }
        }
    }

    /**
     * Catálogo inicial de productos.
     *
     * @return array<int, array<string, mixed>>
     */
    private function productos(): array
    {
        return [
            [
                'nombre' => 'Taladro percutor 750W',
                'categoria' => 'Herramientas',
                'unidad' => 'Unidad',
                'descripcion' => 'Taladro percutor con velocidad variable y empuñadura ergonómica.',
                'precio_compra' => 55,
                'precio_venta' => 89,
                'stock_minimo' => 5,
                'cantidad_inicial' => 12,
            ],
            [
                'nombre' => 'Juego de destornilladores',
                'categoria' => 'Herramientas',
                'unidad' => 'Unidad',
                'descripcion' => 'Juego de seis piezas con puntas planas y Phillips.',
                'precio_compra' => 12,
                'precio_venta' => 22,
                'stock_minimo' => 4,
                'cantidad_inicial' => 20,
            ],
            [
                'nombre' => 'Martillo de bola',
                'categoria' => 'Herramientas',
                'unidad' => 'Unidad',
                'descripcion' => 'Cabezal de acero forjado con mango de madera.',
                'precio_compra' => 8,
                'precio_venta' => 14,
                'stock_minimo' => 3,
                'cantidad_inicial' => 2,
            ],
            [
                'nombre' => 'Caja corrugada 40×30',
                'categoria' => 'Empaque',
                'unidad' => 'Unidad',
                'descripcion' => 'Caja de cartón corrugado de doble pared para despacho.',
                'precio_compra' => 1.2,
                'precio_venta' => 2.5,
                'stock_minimo' => 40,
                'cantidad_inicial' => 320,
            ],
            [
                'nombre' => 'Cinta de embalaje 50 m',
                'categoria' => 'Empaque',
                'unidad' => 'Unidad',
                'descripcion' => 'Rollo transparente de alta adherencia para sellado.',
                'precio_compra' => 1.8,
                'precio_venta' => 3.2,
                'stock_minimo' => 50,
                'cantidad_inicial' => 286,
            ],
            [
                'nombre' => 'Etiqueta térmica 100×150',
                'categoria' => 'Empaque',
                'unidad' => 'Unidad',
                'descripcion' => 'Etiqueta adhesiva para impresión térmica de envíos.',
                'precio_compra' => 0.25,
                'precio_venta' => 0.6,
                'stock_minimo' => 100,
                'cantidad_inicial' => 60,
            ],
            [
                'nombre' => 'Guantes de manipulación',
                'categoria' => 'Insumos',
                'unidad' => 'Unidad',
                'descripcion' => 'Guantes de protección con recubrimiento antideslizante.',
                'precio_compra' => 1.5,
                'precio_venta' => 3,
                'stock_minimo' => 30,
                'cantidad_inicial' => 20,
            ],
            [
                'nombre' => 'Detergente industrial',
                'categoria' => 'Insumos',
                'unidad' => 'Litro',
                'descripcion' => 'Limpiador concentrado para superficies y equipos.',
                'precio_compra' => 4,
                'precio_venta' => 7.5,
                'stock_minimo' => 10,
                'cantidad_inicial' => 60,
            ],
            [
                'nombre' => 'Escáner de código de barras',
                'categoria' => 'Equipos',
                'unidad' => 'Unidad',
                'descripcion' => 'Lector USB con soporte para recepción y despacho.',
                'precio_compra' => 95,
                'precio_venta' => 149,
                'stock_minimo' => 4,
                'cantidad_inicial' => 8,
            ],
            [
                'nombre' => 'Carretilla hidráulica',
                'categoria' => 'Equipos',
                'unidad' => 'Unidad',
                'descripcion' => 'Transpaleta manual con capacidad de 2.5 toneladas.',
                'precio_compra' => 420,
                'precio_venta' => 610,
                'stock_minimo' => 2,
                'cantidad_inicial' => 5,
            ],
            [
                'nombre' => 'Lubricante multiuso',
                'categoria' => 'Otros',
                'unidad' => 'Litro',
                'descripcion' => 'Aceite penetrante para mantenimiento de equipos.',
                'precio_compra' => 6,
                'precio_venta' => 11,
                'stock_minimo' => 8,
                'cantidad_inicial' => 5,
            ],
        ];
    }
}
