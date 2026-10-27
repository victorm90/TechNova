<?php

use App\Models\Almacen;
use App\Models\CategoriaProducto;
use App\Models\Inventario;
use App\Models\MovimientoInventario;
use App\Models\OperacionInventario;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\UnidadMedida;
use App\Models\User;
use App\Services\InventarioService;
use App\Services\ProductoService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->operador = User::factory()->create();

    $this->categoria = CategoriaProducto::factory()->create(['nombre' => 'Herramientas']);
    $this->otraCategoria = CategoriaProducto::factory()->create(['nombre' => 'Empaque']);
    $this->unidad = UnidadMedida::query()->create(['nombre' => 'Unidad', 'abreviatura' => 'ud']);
    $this->almacen = Almacen::factory()->create(['nombre' => 'Almacén principal']);
});

/**
 * Crear un producto y, opcionalmente, su existencia inicial mediante una entrada real.
 *
 * @param  array<string, mixed>  $overrides
 */
function crearProductoDePrueba(array $overrides = []): Producto
{
    $categoria = CategoriaProducto::query()->where('nombre', 'Herramientas')->firstOrFail();
    $unidad = UnidadMedida::query()->where('nombre', 'Unidad')->firstOrFail();
    $almacen = Almacen::query()->where('nombre', 'Almacén principal')->firstOrFail();
    $admin = User::query()->where('role', User::ROLE_ADMIN)->first();

    $producto = app(ProductoService::class)->crear([
        'nombre' => $overrides['nombre'] ?? 'Producto '.fake()->unique()->numerify('###'),
        'categoria_producto_id' => $overrides['categoria_producto_id'] ?? $categoria->id,
        'unidad_medida_id' => $overrides['unidad_medida_id'] ?? $unidad->id,
    ]);

    $precioCompra = (float) ($overrides['precio_compra'] ?? 5);
    $stockMinimo = (int) ($overrides['stock_minimo'] ?? 2);
    $cantidadInicial = (int) ($overrides['cantidad_inicial'] ?? 10);

    /** @var InventarioService $inventario */
    $inventario = app(InventarioService::class);

    if ($cantidadInicial > 0) {
        $inventario->registrarEntrada([
            'almacen_id' => $overrides['almacen_id'] ?? $almacen->id,
            'lineas' => [[
                'producto_id' => $producto->id,
                'cantidad' => $cantidadInicial,
                'precio_compra' => $precioCompra,
                'stock_minimo' => $stockMinimo,
            ]],
        ], $admin);
    } else {
        $inventario->asegurarRegistro($producto->id, $almacen->id);
        $producto->update([
            'precio_compra' => $precioCompra,
            'stock_minimo' => $stockMinimo,
        ]);
    }

    return $producto->refresh();
}

/**
 * Payload válido para crear un producto desde el formulario.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function payloadProducto(array $overrides = []): array
{
    $categoria = CategoriaProducto::query()->where('nombre', 'Herramientas')->firstOrFail();
    $unidad = UnidadMedida::query()->where('nombre', 'Unidad')->firstOrFail();

    return array_merge([
        'nombre' => 'Taladro percutor',
        'categoria_producto_id' => $categoria->id,
        'unidad_medida_id' => $unidad->id,
    ], $overrides);
}

test('las rutas del módulo requieren autenticación', function () {
    foreach ([
        route('productos.index'),
        route('productos.create'),
        route('inventario.index'),
        route('entradas.create'),
        route('salidas.create'),
        route('ajustes.create'),
        route('proveedores.index'),
        route('categorias.index'),
        route('almacenes.index'),
    ] as $url) {
        $this->get($url)->assertRedirect(route('login'));
    }

    $this->post(route('productos.store'), payloadProducto())->assertRedirect(route('login'));
});

test('un operador puede consultar inventario y registrar movimientos pero no administrar catálogos', function () {
    $producto = crearProductoDePrueba();

    $this->actingAs($this->operador);

    $this->get(route('inventario.index'))->assertOk();
    $this->get(route('productos.index'))->assertOk();
    $this->get(route('productos.show', $producto))->assertOk();
    $this->get(route('entradas.create'))->assertOk();
    $this->get(route('salidas.create'))->assertOk();
    $this->get(route('ajustes.create'))->assertOk();

    $this->get(route('productos.create'))->assertForbidden();
    $this->get(route('proveedores.index'))->assertForbidden();
    $this->get(route('categorias.index'))->assertForbidden();
    $this->get(route('almacenes.index'))->assertForbidden();

    $this->post(route('productos.store'), payloadProducto())->assertForbidden();
    $this->post(route('proveedores.store'), ['nombre' => 'Proveedor no autorizado'])->assertForbidden();
});

test('el SKU se genera de forma consecutiva y la creación no registra inventario', function () {
    $this->actingAs($this->admin);

    $this->post(route('productos.store'), payloadProducto())
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $this->post(route('productos.store'), payloadProducto(['nombre' => 'Martillo de bola']))
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $productos = Producto::query()->orderBy('id')->get();

    expect($productos)->toHaveCount(2)
        ->and($productos[0]->sku)->toBe('PROD-000001')
        ->and($productos[1]->sku)->toBe('PROD-000002');

    expect(Inventario::query()->count())->toBe(0)
        ->and(MovimientoInventario::query()->count())->toBe(0)
        ->and(OperacionInventario::query()->count())->toBe(0);
});

test('el producto usa el placeholder cuando no se sube una imagen', function () {
    $this->actingAs($this->admin);

    $this->post(route('productos.store'), payloadProducto())->assertSessionHasNoErrors();

    $producto = Producto::query()->firstOrFail();

    expect($producto->imagen)->toBeNull()
        ->and($producto->imagenUrl())->toEndWith('assets/images/no-data.png');
});

test('la imagen del producto se guarda en el disco público', function () {
    Storage::fake('public');

    $this->actingAs($this->admin);

    $this->post(route('productos.store'), payloadProducto([
        'imagen' => UploadedFile::fake()->image('producto.jpg'),
    ]))->assertSessionHasNoErrors();

    $producto = Producto::query()->firstOrFail();

    expect($producto->imagen)->not->toBeNull();
    Storage::disk('public')->assertExists($producto->imagen);
});

test('no se permite duplicar el nombre de un producto dentro de la misma categoría', function () {
    $this->actingAs($this->admin);

    $this->post(route('productos.store'), payloadProducto())->assertSessionHasNoErrors();

    $this->post(route('productos.store'), payloadProducto())
        ->assertSessionHasErrors('nombre');

    expect(Producto::query()->count())->toBe(1);
});

test('el nombre del producto puede repetirse en categorías distintas', function () {
    $this->actingAs($this->admin);

    $this->post(route('productos.store'), payloadProducto())->assertSessionHasNoErrors();

    $this->post(route('productos.store'), payloadProducto([
        'categoria_producto_id' => $this->otraCategoria->id,
    ]))->assertSessionHasNoErrors();

    expect(Producto::query()->where('nombre', 'Taladro percutor')->count())->toBe(2);
});

test('la base de datos impide duplicar el nombre del producto en la misma categoría', function () {
    Producto::factory()->create([
        'nombre' => 'Cinta adhesiva',
        'categoria_producto_id' => $this->categoria->id,
        'unidad_medida_id' => $this->unidad->id,
    ]);

    expect(fn () => Producto::query()->create([
        'sku' => 'PROD-999999',
        'nombre' => 'Cinta adhesiva',
        'categoria_producto_id' => $this->categoria->id,
        'unidad_medida_id' => $this->unidad->id,
        'precio_compra' => 1,
        'precio_venta' => 2,
        'stock_minimo' => 0,
        'activo' => true,
    ]))->toThrow(QueryException::class);
});

test('no se permite duplicar proveedores por nombre ni identificación fiscal', function () {
    $this->actingAs($this->admin);

    $datos = [
        'nombre' => 'Suministros del Norte',
        'identificacion_fiscal' => 'RIF-J-12345678-9',
        'activo' => 1,
    ];

    $this->post(route('proveedores.store'), $datos)->assertSessionHasNoErrors();

    $this->post(route('proveedores.store'), $datos)->assertSessionHasErrors('nombre');

    $this->post(route('proveedores.store'), [
        'nombre' => 'Otro proveedor',
        'identificacion_fiscal' => 'RIF-J-12345678-9',
        'activo' => 1,
    ])->assertSessionHasErrors('identificacion_fiscal');

    expect(Proveedor::query()->count())->toBe(1);
});

test('la base de datos impide duplicar el nombre de un proveedor', function () {
    Proveedor::query()->create(['nombre' => 'Proveedor único', 'activo' => true]);

    expect(fn () => Proveedor::query()->create(['nombre' => 'Proveedor único', 'activo' => true]))
        ->toThrow(QueryException::class);
});

test('una entrada con varias líneas actualiza existencias, costo promedio y stock mínimo', function () {
    $this->actingAs($this->admin);

    $productoA = crearProductoDePrueba([
        'nombre' => 'Producto A',
        'precio_compra' => 5,
        'cantidad_inicial' => 10,
    ]);

    $productoB = crearProductoDePrueba([
        'nombre' => 'Producto B',
        'precio_compra' => 20,
        'cantidad_inicial' => 5,
    ]);

    $response = $this->post(route('entradas.store'), [
        'almacen_id' => $this->almacen->id,
        'lineas' => [
            ['producto_id' => $productoA->id, 'cantidad' => 10, 'precio_compra' => 15, 'stock_minimo' => 4],
            ['producto_id' => $productoB->id, 'cantidad' => 5, 'precio_compra' => 25, 'stock_minimo' => 3],
        ],
    ]);

    $operacion = OperacionInventario::query()
        ->where('tipo', OperacionInventario::TIPO_ENTRADA)
        ->latest('id')
        ->firstOrFail();

    $response->assertRedirect(route('entradas.show', $operacion));

    expect($operacion->lineas()->count())->toBe(2)
        ->and((float) $operacion->total)->toBe(275.0)
        ->and($operacion->movimientos()->count())->toBe(2);

    $inventarioA = Inventario::query()
        ->where('producto_id', $productoA->id)
        ->where('almacen_id', $this->almacen->id)
        ->firstOrFail();

    $inventarioB = Inventario::query()
        ->where('producto_id', $productoB->id)
        ->where('almacen_id', $this->almacen->id)
        ->firstOrFail();

    expect((int) $inventarioA->cantidad)->toBe(20)
        ->and((float) $inventarioA->costo_promedio)->toBe(10.0)
        ->and((int) $inventarioB->cantidad)->toBe(10)
        ->and((float) $inventarioB->costo_promedio)->toBe(22.5)
        ->and((float) $productoA->fresh()->precio_compra)->toBe(15.0)
        ->and((int) $productoA->fresh()->stock_minimo)->toBe(4)
        ->and((float) $productoB->fresh()->precio_compra)->toBe(25.0)
        ->and((int) $productoB->fresh()->stock_minimo)->toBe(3);

    $costos = $operacion->lineas()->orderBy('producto_id')->pluck('costo_unitario')
        ->map(fn ($costo): float => (float) $costo)
        ->all();

    expect($costos)->toBe([15.0, 25.0]);
});

test('una entrada acepta un proveedor opcional', function () {
    $this->actingAs($this->admin);

    $producto = crearProductoDePrueba();
    $proveedor = Proveedor::query()->create(['nombre' => 'Proveedor existente', 'activo' => true]);

    $this->post(route('entradas.store'), [
        'almacen_id' => $this->almacen->id,
        'proveedor_id' => $proveedor->id,
        'lineas' => [
            ['producto_id' => $producto->id, 'cantidad' => 5, 'precio_compra' => 5, 'stock_minimo' => 2],
        ],
    ])->assertSessionHasNoErrors();

    expect(OperacionInventario::query()->latest('id')->first()?->proveedor_id)->toBe($proveedor->id);

    $this->post(route('entradas.store'), [
        'almacen_id' => $this->almacen->id,
        'lineas' => [
            ['producto_id' => $producto->id, 'cantidad' => 1, 'precio_compra' => 5, 'stock_minimo' => 2],
        ],
    ])->assertSessionHasNoErrors();

    expect(OperacionInventario::query()->latest('id')->first()?->proveedor_id)->toBeNull();
});

test('la entrada rechaza cantidades decimales', function () {
    $this->actingAs($this->admin);

    $producto = crearProductoDePrueba();

    $this->post(route('entradas.store'), [
        'almacen_id' => $this->almacen->id,
        'lineas' => [
            ['producto_id' => $producto->id, 'cantidad' => 1.5, 'precio_compra' => 5, 'stock_minimo' => 2],
        ],
    ])->assertSessionHasErrors('lineas.0.cantidad');
});

test('una salida con varias líneas descuenta el stock y registra movimientos', function () {
    $this->actingAs($this->admin);

    $productoA = crearProductoDePrueba(['nombre' => 'Salida A']);
    $productoB = crearProductoDePrueba(['nombre' => 'Salida B']);

    $response = $this->post(route('salidas.store'), [
        'almacen_id' => $this->almacen->id,
        'referencia' => 'DESP-1',
        'lineas' => [
            ['producto_id' => $productoA->id, 'cantidad' => 4],
            ['producto_id' => $productoB->id, 'cantidad' => 6],
        ],
    ]);

    $operacion = OperacionInventario::query()
        ->where('tipo', OperacionInventario::TIPO_SALIDA)
        ->latest('id')
        ->firstOrFail();

    $response->assertRedirect(route('salidas.show', $operacion));

    expect((float) $operacion->total)->toBe(50.0)
        ->and($operacion->movimientos()->count())->toBe(2);

    expect((int) Inventario::query()->where('producto_id', $productoA->id)->sum('cantidad'))->toBe(6)
        ->and((int) Inventario::query()->where('producto_id', $productoB->id)->sum('cantidad'))->toBe(4);
});

test('una salida con stock insuficiente revierte toda la operación', function () {
    $this->actingAs($this->admin);

    $productoA = crearProductoDePrueba(['nombre' => 'Rollback A', 'cantidad_inicial' => 10]);
    $productoB = crearProductoDePrueba(['nombre' => 'Rollback B', 'cantidad_inicial' => 10]);

    $this->post(route('salidas.store'), [
        'almacen_id' => $this->almacen->id,
        'lineas' => [
            ['producto_id' => $productoA->id, 'cantidad' => 5],
            ['producto_id' => $productoB->id, 'cantidad' => 999],
        ],
    ])->assertSessionHasErrors();

    expect(OperacionInventario::query()->where('tipo', OperacionInventario::TIPO_SALIDA)->count())->toBe(0);

    expect((int) Inventario::query()->where('producto_id', $productoA->id)->sum('cantidad'))->toBe(10)
        ->and((int) Inventario::query()->where('producto_id', $productoB->id)->sum('cantidad'))->toBe(10);
});

test('no se permite repetir el mismo producto en una operación', function () {
    $this->actingAs($this->admin);

    $producto = crearProductoDePrueba();

    $this->post(route('entradas.store'), [
        'almacen_id' => $this->almacen->id,
        'lineas' => [
            ['producto_id' => $producto->id, 'cantidad' => 2, 'precio_compra' => 5, 'stock_minimo' => 2],
            ['producto_id' => $producto->id, 'cantidad' => 3, 'precio_compra' => 5, 'stock_minimo' => 2],
        ],
    ])->assertSessionHasErrors();

    expect(OperacionInventario::query()->count())->toBe(1); // solo la entrada inicial
});

test('el servicio de inventario rechaza salidas sin existencias', function () {
    $producto = crearProductoDePrueba(['cantidad_inicial' => 0]);

    expect(fn () => app(InventarioService::class)->registrarSalida([
        'almacen_id' => $this->almacen->id,
        'lineas' => [
            ['producto_id' => $producto->id, 'cantidad' => 1],
        ],
    ], $this->admin))->toThrow(ValidationException::class);
});

test('un ajuste solo cambia el stock y conserva el precio del producto', function () {
    $this->actingAs($this->admin);

    $producto = crearProductoDePrueba([
        'cantidad_inicial' => 10,
        'precio_compra' => 5,
        'stock_minimo' => 2,
    ]);

    $inventario = Inventario::query()
        ->where('producto_id', $producto->id)
        ->where('almacen_id', $this->almacen->id)
        ->firstOrFail();

    $precioAntes = (float) $producto->precio_compra;
    $costoAntes = (float) $inventario->costo_promedio;

    $response = $this->post(route('ajustes.store'), [
        'inventario_id' => $inventario->id,
        'cantidad' => 7,
    ]);

    $operacion = OperacionInventario::query()
        ->where('tipo', OperacionInventario::TIPO_AJUSTE)
        ->firstOrFail();

    $movimiento = $operacion->movimientos()->firstOrFail();

    $response->assertRedirect(route('ajustes.show', $operacion));

    expect((int) $inventario->fresh()->cantidad)->toBe(7)
        ->and((float) $inventario->fresh()->costo_promedio)->toBe($costoAntes)
        ->and((float) $producto->fresh()->precio_compra)->toBe($precioAntes)
        ->and((float) $operacion->total)->toBe(0.0)
        ->and($operacion->lineas()->count())->toBe(1)
        ->and((int) $movimiento->cantidad)->toBe(-3)
        ->and((int) $movimiento->saldo_resultante)->toBe(7);
});

test('un ajuste no permite inventar existencias ni crear productos', function () {
    $this->actingAs($this->admin);

    crearProductoDePrueba();

    $this->post(route('ajustes.store'), [
        'inventario_id' => 999999,
        'cantidad' => 5,
    ])->assertSessionHasErrors('inventario_id');

    expect(Producto::query()->count())->toBe(1)
        ->and(OperacionInventario::query()->where('tipo', OperacionInventario::TIPO_AJUSTE)->count())->toBe(0);
});

test('el formulario de entrada permite elegir el almacén de destino', function () {
    crearProductoDePrueba();

    $this->actingAs($this->admin);

    $this->get(route('entradas.create'))
        ->assertOk()
        ->assertSee('name="almacen_id"', false)
        ->assertSee('Almacén de destino')
        ->assertSee('Almacén principal')
        ->assertSee('Nuevo almacén')
        ->assertSee('Nuevo proveedor');
});

test('una entrada se registra en el almacén seleccionado', function () {
    $almacenSecundario = Almacen::factory()->create(['nombre' => 'Almacén secundario']);
    $producto = crearProductoDePrueba(['cantidad_inicial' => 2]);

    $this->actingAs($this->admin);

    $this->post(route('entradas.store'), [
        'almacen_id' => $almacenSecundario->id,
        'lineas' => [
            ['producto_id' => $producto->id, 'cantidad' => 4, 'precio_compra' => 5, 'stock_minimo' => 2],
        ],
    ])->assertSessionHasNoErrors();

    expect((int) Inventario::query()
        ->where('producto_id', $producto->id)
        ->where('almacen_id', $almacenSecundario->id)
        ->value('cantidad'))->toBe(4)
        ->and((int) Inventario::query()
            ->where('producto_id', $producto->id)
            ->where('almacen_id', $this->almacen->id)
            ->value('cantidad'))->toBe(2);
});

test('un operador puede crear almacenes y proveedores rápidos desde la entrada', function () {
    $this->actingAs($this->operador);

    $this->postJson(route('entradas.almacenes.store'), ['nombre' => 'Almacén temporal'])
        ->assertCreated()
        ->assertJsonPath('nombre', 'Almacén temporal');

    $this->postJson(route('entradas.proveedores.store'), ['nombre' => 'Proveedor temporal'])
        ->assertCreated()
        ->assertJsonPath('nombre', 'Proveedor temporal');

    expect(Almacen::query()->where('nombre', 'Almacén temporal')->where('activo', true)->exists())->toBeTrue()
        ->and(Proveedor::query()->where('nombre', 'Proveedor temporal')->where('activo', true)->exists())->toBeTrue();
});

test('la creación rápida no permite nombres repetidos', function () {
    Almacen::factory()->create(['nombre' => 'Almacén repetido']);
    Proveedor::query()->create(['nombre' => 'Proveedor repetido', 'activo' => true]);

    $this->actingAs($this->admin);

    $this->postJson(route('entradas.almacenes.store'), ['nombre' => 'Almacén repetido'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('nombre');

    $this->postJson(route('entradas.proveedores.store'), ['nombre' => 'Proveedor repetido'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('nombre');
});

test('la vista de inventario distingue stock bajo, cercano y saludable', function () {
    crearProductoDePrueba([
        'nombre' => 'Producto bajo',
        'precio_compra' => 1,
        'stock_minimo' => 10,
        'cantidad_inicial' => 5,
    ]);

    crearProductoDePrueba([
        'nombre' => 'Producto cerca',
        'precio_compra' => 1,
        'stock_minimo' => 10,
        'cantidad_inicial' => 10,
    ]);

    crearProductoDePrueba([
        'nombre' => 'Producto bien',
        'precio_compra' => 1,
        'stock_minimo' => 10,
        'cantidad_inicial' => 20,
    ]);

    $this->actingAs($this->admin)
        ->get(route('inventario.index'))
        ->assertOk()
        ->assertSee('Por debajo del mínimo')
        ->assertSee('Cerca del mínimo')
        ->assertSee('Stock saludable')
        ->assertSee('text-rose-600', false)
        ->assertSee('text-amber-600', false)
        ->assertSee('text-emerald-600', false);
});
