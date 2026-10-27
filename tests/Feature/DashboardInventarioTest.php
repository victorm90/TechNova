<?php

use App\Models\Almacen;
use App\Models\CategoriaProducto;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\UnidadMedida;
use App\Models\User;
use App\Services\InventarioService;
use App\Services\ProductoService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('el dashboard muestra indicadores reales cuando existen productos', function () {
    $admin = User::factory()->admin()->create();
    $categoria = CategoriaProducto::factory()->create(['nombre' => 'Herramientas']);
    $unidad = UnidadMedida::query()->create(['nombre' => 'Unidad', 'abreviatura' => 'ud']);
    $almacen = Almacen::factory()->create(['nombre' => 'Almacén principal']);

    $producto = app(ProductoService::class)->crear([
        'nombre' => 'Taladro de prueba',
        'categoria_producto_id' => $categoria->id,
        'unidad_medida_id' => $unidad->id,
    ]);

    $producto->update(['precio_venta' => 18]);

    app(InventarioService::class)->registrarEntrada([
        'almacen_id' => $almacen->id,
        'lineas' => [[
            'producto_id' => $producto->id,
            'cantidad' => 3,
            'precio_compra' => 10,
            'stock_minimo' => 5,
        ]],
    ], $admin);

    $vendor = app(ProductoService::class)->crear([
        'nombre' => 'Producto sin stock',
        'categoria_producto_id' => $categoria->id,
        'unidad_medida_id' => $unidad->id,
    ]);

    $vendor->update([
        'precio_venta' => 4,
        'stock_minimo' => 1,
    ]);

    $valor = (float) Inventario::query()
        ->selectRaw('COALESCE(SUM(cantidad * costo_promedio), 0) as valor')
        ->value('valor');

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Inventario real')
        ->assertSee('Stock bajo')
        ->assertSee($producto->nombre)
        ->assertViewHas('mock', function (array $mock) use ($valor, $producto, $vendor): bool {
            return (float) $mock['kpis'][0]['value'] === $valor
                && $mock['kpis'][1]['value'] === 2
                && collect($mock['low_stock'])->pluck('product')->contains($producto->nombre)
                && collect($mock['low_stock'])->pluck('product')->contains($vendor->nombre);
        });
});

test('la página de presentación muestra el catálogo real de productos activos', function () {
    $admin = User::factory()->admin()->create();
    $categoria = CategoriaProducto::factory()->create(['nombre' => 'Empaque']);
    $unidad = UnidadMedida::query()->create(['nombre' => 'Unidad', 'abreviatura' => 'ud']);
    $almacen = Almacen::factory()->create(['nombre' => 'Almacén principal']);

    $producto = app(ProductoService::class)->crear([
        'nombre' => 'Caja corrugada visible',
        'categoria_producto_id' => $categoria->id,
        'unidad_medida_id' => $unidad->id,
    ]);

    $producto->update([
        'descripcion' => 'Caja para despacho',
        'precio_venta' => 2.5,
    ]);

    app(InventarioService::class)->registrarEntrada([
        'almacen_id' => $almacen->id,
        'lineas' => [[
            'producto_id' => $producto->id,
            'cantidad' => 5,
            'precio_compra' => 1,
            'stock_minimo' => 10,
        ]],
    ], $admin);

    Producto::factory()->inactivo()->create([
        'nombre' => 'Producto oculto',
        'categoria_producto_id' => $categoria->id,
        'unidad_medida_id' => $unidad->id,
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Catálogo real')
        ->assertSee('Caja corrugada visible')
        ->assertSee('Stock bajo')
        ->assertDontSee('Producto oculto')
        ->assertViewHas('usandoProductosReales', true)
        ->assertViewHas('featuredProducts', function (array $productos): bool {
            return count($productos) === 1
                && $productos[0]['sku'] === 'PROD-000001'
                && $productos[0]['status'] === 'low';
        });
});
