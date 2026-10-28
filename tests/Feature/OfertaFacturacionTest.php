<?php

use App\Models\Almacen;
use App\Models\Customer;
use App\Models\Factura;
use App\Models\FacturaLinea;
use App\Models\Gasto;
use App\Models\HistorialPrecio;
use App\Models\Inventario;
use App\Models\MovimientoInventario;
use App\Models\Oferta;
use App\Models\OfertaServicio;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\UnidadMedida;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->operador = User::factory()->create();

    $this->unidad = UnidadMedida::factory()->create([
        'nombre' => 'Unidad',
        'abreviatura' => 'ud',
    ]);

    $this->cliente = Customer::factory()->create();

    $this->servicio = Servicio::factory()->create([
        'nombre' => 'Instalación de panel',
        'unidad_medida_id' => $this->unidad->id,
        'precio_unitario' => 100,
    ]);

    $this->gasto = Gasto::factory()->create([
        'nombre' => 'Transporte',
        'precio_unitario' => 50,
    ]);

    $this->almacen = Almacen::factory()->create([
        'nombre' => 'Almacén central',
    ]);

    $this->producto = Producto::factory()->create([
        'nombre' => 'Cable 2.5mm',
        'unidad_medida_id' => $this->unidad->id,
        'precio_venta' => 20,
    ]);

    $this->inventario = Inventario::factory()->create([
        'producto_id' => $this->producto->id,
        'almacen_id' => $this->almacen->id,
        'cantidad' => 10,
        'costo_promedio' => 12,
    ]);

    $this->actingAs($this->operador);
});

/**
 * Oferta con un servicio que consume un producto de almacén.
 */
function ofertaConProducto(
    Customer $cliente,
    Almacen $almacen,
    Servicio $servicio,
    Producto $producto,
    int $cantidad = 2,
    float $precioVenta = 20.0,
): Oferta {
    $oferta = Oferta::factory()->create([
        'customer_id' => $cliente->id,
        'almacen_id' => $almacen->id,
        'fecha' => '2026-10-28',
    ]);

    $linea = $oferta->servicios()->create([
        'servicio_id' => $servicio->id,
        'cantidad' => 1,
        'precio_unitario' => 100,
    ]);

    $linea->productos()->create([
        'producto_id' => $producto->id,
        'cantidad' => $cantidad,
        'precio_venta' => $precioVenta,
    ]);

    return $oferta;
}

test('el alta rápida crea la oferta y el cliente con solo el nombre', function () {
    $this->post(route('ofertas.storeRapida'), [
        'cliente_nombre' => 'Cliente Nuevo',
        'fecha' => '2026-10-28',
    ])->assertRedirect()->assertSessionHasNoErrors();

    $oferta = Oferta::query()->with('customer')->firstOrFail();

    expect($oferta->codigo)->toBe('OF-000001')
        ->and($oferta->estado)->toBe(Oferta::ESTADO_BORRADOR)
        ->and($oferta->fecha->toDateString())->toBe('2026-10-28')
        ->and($oferta->customer->name)->toBe('Cliente Nuevo')
        ->and($oferta->customer->phone)->toBeNull()
        ->and($oferta->customer->address)->toBeNull();

    $this->assertDatabaseHas('clientes', [
        'name' => 'Cliente Nuevo',
        'phone' => null,
        'address' => null,
        'identity_card' => null,
    ]);
});

test('el alta rápida reutiliza el cliente existente con el mismo nombre', function () {
    $this->post(route('ofertas.storeRapida'), [
        'cliente_nombre' => $this->cliente->name,
        'fecha' => '2026-10-28',
    ])->assertRedirect()->assertSessionHasNoErrors();

    $oferta = Oferta::query()->firstOrFail();

    expect(Customer::query()->count())->toBe(1)
        ->and($oferta->customer_id)->toBe($this->cliente->id);
});

test('el alta rápida exige el nombre del cliente', function () {
    $this->post(route('ofertas.storeRapida'), [
        'cliente_nombre' => '',
    ])->assertSessionHasErrors('cliente_nombre');

    expect(Oferta::query()->count())->toBe(0);
});

test('cambiar el precio de una línea registra el historial con el usuario', function () {
    $oferta = Oferta::factory()->create(['customer_id' => $this->cliente->id]);
    $linea = $oferta->servicios()->create([
        'servicio_id' => $this->servicio->id,
        'cantidad' => 1,
        'precio_unitario' => 100,
    ]);

    $this->put(route('ofertas.update', $oferta), [
        'customer_id' => $this->cliente->id,
        'fecha' => '2026-10-28',
        'estado' => Oferta::ESTADO_BORRADOR,
        'servicios' => [[
            'id' => $linea->id,
            'servicio_id' => $this->servicio->id,
            'cantidad' => '1',
            'precio_unitario' => '120',
        ]],
        'gastos' => [],
    ])->assertRedirect(route('ofertas.show', $oferta))->assertSessionHasNoErrors();

    $historial = HistorialPrecio::query()->firstOrFail();

    expect(HistorialPrecio::query()->count())->toBe(1)
        ->and($historial->historialable_type)->toBe(OfertaServicio::class)
        ->and($historial->historialable_id)->toBe($linea->id)
        ->and((float) $historial->precio_anterior)->toBe(100.0)
        ->and((float) $historial->precio_nuevo)->toBe(120.0)
        ->and($historial->user_id)->toBe($this->operador->id);

    expect((float) $linea->fresh()->precio_unitario)->toBe(120.0)
        ->and((float) $this->servicio->fresh()->precio_unitario)->toBe(100.0);
});

test('los modales del detalle agregan líneas y registran su historial', function () {
    $oferta = Oferta::factory()->create([
        'customer_id' => $this->cliente->id,
        'almacen_id' => $this->almacen->id,
    ]);

    $this->post(route('ofertas.servicios.store', $oferta), [
        'servicio_id' => $this->servicio->id,
        'cantidad' => '2',
        'precio_unitario' => '95',
    ])->assertRedirect()->assertSessionHasNoErrors();

    $linea = $oferta->servicios()->firstOrFail();

    expect((float) $linea->precio_unitario)->toBe(95.0)
        ->and((float) $linea->cantidad)->toBe(2.0);

    $this->post(route('ofertas.servicios.productos.store', [$oferta, $linea]), [
        'producto_id' => $this->producto->id,
        'cantidad' => '2',
        'precio_venta' => '22',
    ])->assertRedirect()->assertSessionHasNoErrors();

    $this->post(route('ofertas.gastos.store', $oferta), [
        'gasto_id' => $this->gasto->id,
        'cantidad' => '1',
        'precio_unitario' => '60',
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect($oferta->servicios()->count())->toBe(1)
        ->and($oferta->gastos()->count())->toBe(1)
        ->and($oferta->servicios()->firstOrFail()->productos()->count())->toBe(1)
        ->and(HistorialPrecio::query()->count())->toBe(3)
        ->and((float) $this->servicio->fresh()->precio_unitario)->toBe(100.0)
        ->and((float) $this->gasto->fresh()->precio_unitario)->toBe(50.0)
        ->and((float) $this->producto->fresh()->precio_venta)->toBe(20.0);

    $productoSinStock = Producto::factory()->create(['precio_venta' => 10]);

    $this->post(route('ofertas.servicios.productos.store', [$oferta, $linea]), [
        'producto_id' => $productoSinStock->id,
        'cantidad' => '1',
        'precio_venta' => '10',
    ])->assertSessionHasErrors('stock');

    expect($linea->productos()->count())->toBe(1);
});

test('la oferta guarda productos del almacén y suma los materiales al total', function () {
    $this->post(route('ofertas.store'), [
        'customer_id' => $this->cliente->id,
        'fecha' => '2026-10-28',
        'estado' => Oferta::ESTADO_BORRADOR,
        'almacen_id' => $this->almacen->id,
        'servicios' => [[
            'servicio_id' => $this->servicio->id,
            'cantidad' => '1',
            'precio_unitario' => '100',
            'productos' => [[
                'producto_id' => $this->producto->id,
                'cantidad' => '2',
                'precio_venta' => '20',
            ]],
        ]],
        'gastos' => [],
    ])->assertRedirect()->assertSessionHasNoErrors();

    $oferta = Oferta::query()
        ->with(['servicios.productos'])
        ->firstOrFail();

    $lineaProducto = $oferta->servicios->first()->productos->first();

    expect($oferta->total_servicios)->toBe(100.0)
        ->and($oferta->total_productos)->toBe(40.0)
        ->and($oferta->total)->toBe(140.0)
        ->and($lineaProducto->cantidad)->toBe(2);

    $this->assertDatabaseHas('oferta_servicio_productos', [
        'oferta_servicio_id' => $oferta->servicios->first()->id,
        'producto_id' => $this->producto->id,
        'cantidad' => 2,
        'precio_venta' => 20,
    ]);

    expect((float) $this->producto->fresh()->precio_venta)->toBe(20.0);
});

test('la oferta rechaza productos que superan el stock disponible', function () {
    $this->inventario->update(['cantidad' => 3]);

    $this->post(route('ofertas.store'), [
        'customer_id' => $this->cliente->id,
        'fecha' => '2026-10-28',
        'estado' => Oferta::ESTADO_BORRADOR,
        'almacen_id' => $this->almacen->id,
        'servicios' => [[
            'servicio_id' => $this->servicio->id,
            'cantidad' => '1',
            'precio_unitario' => '100',
            'productos' => [[
                'producto_id' => $this->producto->id,
                'cantidad' => '5',
                'precio_venta' => '20',
            ]],
        ]],
        'gastos' => [],
    ])->assertSessionHasErrors('stock');

    expect(Oferta::query()->count())->toBe(0)
        ->and($this->inventario->fresh()->cantidad)->toBe(3);
});

test('facturar ajusta las líneas reales, descuenta stock y bloquea la oferta', function () {
    $oferta = ofertaConProducto(
        $this->cliente,
        $this->almacen,
        $this->servicio,
        $this->producto,
    );

    $lineaServicio = $oferta->servicios()->firstOrFail();
    $lineaProducto = $lineaServicio->productos()->firstOrFail();

    $this->get(route('ofertas.show', $oferta))
        ->assertOk()
        ->assertSee($this->producto->nombre)
        ->assertSee('Facturar oferta');

    $respuesta = $this->post(route('ofertas.facturar', $oferta), [
        'almacen_id' => $this->almacen->id,
        'fecha' => '2026-10-28',
        'servicios' => [[
            'id' => $lineaServicio->id,
            'cantidad' => '3',
            'precio_unitario' => '90',
        ]],
        'gastos' => [],
        'productos' => [[
            'id' => $lineaProducto->id,
            'cantidad' => '3',
            'precio_venta' => '25',
        ]],
    ]);

    $factura = Factura::query()->with('lineas')->firstOrFail();

    $respuesta->assertRedirect(route('facturas.show', $factura))->assertSessionHasNoErrors();

    expect($factura->codigo)->toBe('FAC-000001')
        ->and((float) $factura->subtotal_servicios)->toBe(270.0)
        ->and((float) $factura->subtotal_productos)->toBe(75.0)
        ->and((float) $factura->subtotal_gastos)->toBe(0.0)
        ->and((float) $factura->total)->toBe(345.0)
        ->and($factura->lineas)->toHaveCount(2);

    $lineaServicioFacturada = $factura->lineas->firstWhere('tipo', FacturaLinea::TIPO_SERVICIO);

    expect($lineaServicioFacturada->descripcion)->toBe('Instalación de panel')
        ->and((float) $lineaServicioFacturada->cantidad)->toBe(3.0)
        ->and((float) $lineaServicioFacturada->precio_unitario)->toBe(90.0)
        ->and((float) $lineaServicioFacturada->subtotal)->toBe(270.0);

    $oferta->refresh();

    expect($oferta->estado)->toBe(Oferta::ESTADO_FACTURADO)
        ->and($oferta->facturada_at)->not->toBeNull()
        ->and((float) $oferta->servicios()->firstOrFail()->cantidad)->toBe(3.0)
        ->and((float) $oferta->servicios()->firstOrFail()->precio_unitario)->toBe(90.0);

    $productoOferta = $oferta->servicios()->firstOrFail()->productos()->firstOrFail();

    expect($productoOferta->cantidad)->toBe(3)
        ->and((float) $productoOferta->precio_venta)->toBe(25.0);

    expect($this->inventario->fresh()->cantidad)->toBe(7);

    $operacion = $factura->operacionesInventario()->firstOrFail();

    expect($operacion->tipo)->toBe('salida')
        ->and($operacion->factura_id)->toBe($factura->id)
        ->and($operacion->user_id)->toBe($this->operador->id);

    $movimiento = MovimientoInventario::query()->where('producto_id', $this->producto->id)->firstOrFail();

    expect($movimiento->tipo)->toBe('salida')
        ->and($movimiento->cantidad)->toBe(3)
        ->and($movimiento->saldo_resultante)->toBe(7)
        ->and($movimiento->operacion_inventario_id)->toBe($operacion->id);

    expect(HistorialPrecio::query()->count())->toBe(2);

    $this->get(route('facturas.show', $factura))
        ->assertOk()
        ->assertSee($factura->codigo)
        ->assertSee('345,00');

    $this->get(route('ofertas.show', $oferta))
        ->assertOk()
        ->assertSee('Oferta facturada')
        ->assertSee($factura->codigo);

    $this->get(route('ofertas.edit', $oferta))
        ->assertRedirect(route('ofertas.show', $oferta))
        ->assertSessionHas('error');

    $this->delete(route('ofertas.destroy', $oferta))->assertSessionHas('error');

    $this->assertDatabaseHas('ofertas', ['id' => $oferta->id, 'estado' => Oferta::ESTADO_FACTURADO]);

    $this->post(route('ofertas.facturar', $oferta), [
        'almacen_id' => $this->almacen->id,
        'servicios' => [],
        'gastos' => [],
        'productos' => [],
    ])->assertSessionHasErrors('oferta');
});

test('no se puede facturar si el stock real no alcanza', function () {
    $oferta = ofertaConProducto(
        $this->cliente,
        $this->almacen,
        $this->servicio,
        $this->producto,
        cantidad: 2,
    );

    $this->inventario->update(['cantidad' => 2]);

    $lineaServicio = $oferta->servicios()->firstOrFail();
    $lineaProducto = $lineaServicio->productos()->firstOrFail();

    $this->post(route('ofertas.facturar', $oferta), [
        'almacen_id' => $this->almacen->id,
        'servicios' => [[
            'id' => $lineaServicio->id,
            'cantidad' => '1',
            'precio_unitario' => '100',
        ]],
        'gastos' => [],
        'productos' => [[
            'id' => $lineaProducto->id,
            'cantidad' => '5',
            'precio_venta' => '20',
        ]],
    ])->assertSessionHasErrors('stock');

    expect(Factura::query()->count())->toBe(0)
        ->and($oferta->fresh()->estado)->toBe(Oferta::ESTADO_BORRADOR)
        ->and($this->inventario->fresh()->cantidad)->toBe(2);
});

test('la oferta se puede imprimir y descargar en PDF', function () {
    $oferta = ofertaConProducto(
        $this->cliente,
        $this->almacen,
        $this->servicio,
        $this->producto,
    );

    $this->get(route('ofertas.imprimir', $oferta))
        ->assertOk()
        ->assertSee('OFERTA')
        ->assertSee($oferta->codigo);

    $respuesta = $this->get(route('ofertas.pdf', $oferta));

    $respuesta->assertOk();

    expect((string) $respuesta->headers->get('content-type'))->toContain('application/pdf')
        ->and($respuesta->headers->get('content-disposition'))->toContain('Oferta-'.$oferta->codigo.'.pdf');
});

test('la factura se puede imprimir y descargar en PDF', function () {
    $factura = Factura::factory()->create([
        'oferta_id' => Oferta::factory()->create(['customer_id' => $this->cliente->id])->id,
        'customer_id' => $this->cliente->id,
        'codigo' => 'FAC-000099',
        'total' => 150,
    ]);

    $this->get(route('facturas.imprimir', $factura))
        ->assertOk()
        ->assertSee('FACTURA')
        ->assertSee('FAC-000099');

    $respuesta = $this->get(route('facturas.pdf', $factura));

    $respuesta->assertOk();

    expect((string) $respuesta->headers->get('content-type'))->toContain('application/pdf')
        ->and($respuesta->headers->get('content-disposition'))->toContain('Factura-FAC-000099.pdf');
});
