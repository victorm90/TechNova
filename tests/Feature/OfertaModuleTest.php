<?php

use App\Models\Customer;
use App\Models\Gasto;
use App\Models\Oferta;
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
});

/**
 * Payload válido para crear o actualizar una oferta desde el formulario.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function payloadOferta(Customer $cliente, Servicio $servicio, ?Gasto $gasto = null, array $overrides = []): array
{
    return array_merge([
        'customer_id' => $cliente->id,
        'fecha' => '2026-10-28',
        'estado' => Oferta::ESTADO_BORRADOR,
        'servicios' => [
            [
                'servicio_id' => $servicio->id,
                'cantidad' => '2',
                'precio_unitario' => '100.00',
            ],
        ],
        'gastos' => $gasto
            ? [[
                'gasto_id' => $gasto->id,
                'cantidad' => '1',
                'precio_unitario' => '50.00',
            ]]
            : [],
    ], $overrides);
}

test('las rutas de ofertas y catálogos requieren autenticación', function () {
    foreach ([
        route('ofertas.index'),
        route('ofertas.create'),
        route('gastos.index'),
        route('gastos.create'),
        route('servicios.index'),
        route('servicios.create'),
    ] as $url) {
        $this->get($url)->assertRedirect(route('login'));
    }

    $this->post(route('ofertas.store'), [])->assertRedirect(route('login'));
});

test('un operador puede gestionar ofertas pero no los catálogos de servicios y gastos', function () {
    $this->actingAs($this->operador);

    $this->get(route('ofertas.index'))->assertOk();
    $this->get(route('ofertas.create'))->assertOk();
    $this->get(route('gastos.index'))->assertForbidden();
    $this->get(route('servicios.index'))->assertForbidden();

    $this->post(route('servicios.store'), [
        'nombre' => 'Servicio no autorizado',
        'unidad_medida_id' => $this->unidad->id,
        'precio_unitario' => '10',
    ])->assertForbidden();

    $this->post(route('gastos.store'), [
        'nombre' => 'Gasto no autorizado',
        'precio_unitario' => '10',
    ])->assertForbidden();
});

test('una oferta guarda servicios y gastos y calcula sus totales', function () {
    $this->actingAs($this->operador);

    $payload = payloadOferta($this->cliente, $this->servicio, $this->gasto, [
        'estado' => Oferta::ESTADO_ENVIADA,
        'servicios' => [[
            'servicio_id' => $this->servicio->id,
            'cantidad' => '2.5',
            'precio_unitario' => '100.00',
        ]],
        'gastos' => [[
            'gasto_id' => $this->gasto->id,
            'cantidad' => '3',
            'precio_unitario' => '50.00',
        ]],
    ]);

    $this->post(route('ofertas.store'), $payload)
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $oferta = Oferta::query()->with(['customer', 'servicios', 'gastos'])->firstOrFail();

    expect($oferta->codigo)->toBe('OF-000001')
        ->and($oferta->estado)->toBe(Oferta::ESTADO_ENVIADA)
        ->and($oferta->customer->is($this->cliente))->toBeTrue()
        ->and($oferta->servicios)->toHaveCount(1)
        ->and($oferta->gastos)->toHaveCount(1)
        ->and($oferta->servicios->first()->total)->toBe(250.0)
        ->and($oferta->gastos->first()->total)->toBe(150.0)
        ->and($oferta->total_servicios)->toBe(250.0)
        ->and($oferta->total_gastos)->toBe(150.0)
        ->and($oferta->total)->toBe(400.0);

    $this->assertDatabaseHas('oferta_servicios', [
        'oferta_id' => $oferta->id,
        'servicio_id' => $this->servicio->id,
        'cantidad' => 2.5,
        'precio_unitario' => 100,
    ]);

    $this->assertDatabaseHas('oferta_gastos', [
        'oferta_id' => $oferta->id,
        'gasto_id' => $this->gasto->id,
        'cantidad' => 3,
        'precio_unitario' => 50,
    ]);
});

test('las filas vacías del formulario se descartan antes de guardar la oferta', function () {
    $this->actingAs($this->operador);

    $payload = payloadOferta($this->cliente, $this->servicio, $this->gasto);
    $payload['servicios'][] = ['servicio_id' => '', 'cantidad' => '', 'precio_unitario' => ''];
    $payload['gastos'][] = ['gasto_id' => '', 'cantidad' => '', 'precio_unitario' => ''];

    $this->post(route('ofertas.store'), $payload)
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $oferta = Oferta::query()->withCount(['servicios', 'gastos'])->firstOrFail();

    expect($oferta->servicios_count)->toBe(1)
        ->and($oferta->gastos_count)->toBe(1);
});

test('la oferta valida los campos obligatorios y las líneas vacías', function () {
    $this->actingAs($this->operador);

    $this->post(route('ofertas.store'), [])
        ->assertSessionHasErrors(['customer_id', 'fecha', 'estado', 'servicios']);

    $payload = payloadOferta($this->cliente, $this->servicio, $this->gasto);
    $payload['customer_id'] = 999999;
    $payload['fecha'] = 'no-es-una-fecha';
    $payload['estado'] = 'inventado';
    $payload['servicios'] = [['servicio_id' => '', 'cantidad' => '', 'precio_unitario' => '']];
    $payload['gastos'] = [];

    $this->post(route('ofertas.store'), $payload)
        ->assertSessionHasErrors(['customer_id', 'fecha', 'estado', 'servicios']);
});

test('la oferta rechaza cantidades y precios negativos', function () {
    $this->actingAs($this->operador);

    $payload = payloadOferta($this->cliente, $this->servicio, $this->gasto);
    $payload['servicios'][0]['cantidad'] = '0';
    $payload['servicios'][0]['precio_unitario'] = '-1';
    $payload['gastos'][0]['cantidad'] = '-2';
    $payload['gastos'][0]['precio_unitario'] = '-0.01';

    $this->post(route('ofertas.store'), $payload)
        ->assertSessionHasErrors([
            'servicios.0.cantidad',
            'servicios.0.precio_unitario',
            'gastos.0.cantidad',
            'gastos.0.precio_unitario',
        ]);
});

test('la oferta rechaza servicios o gastos repetidos', function () {
    $this->actingAs($this->operador);

    $payload = payloadOferta($this->cliente, $this->servicio, $this->gasto);
    $payload['servicios'][] = [
        'servicio_id' => $this->servicio->id,
        'cantidad' => '1',
        'precio_unitario' => '100.00',
    ];

    $this->post(route('ofertas.store'), $payload)
        ->assertSessionHasErrors('servicios.1.servicio_id');
});

test('actualizar una oferta reemplaza sus líneas y recalcula el total', function () {
    $this->actingAs($this->operador);

    $oferta = Oferta::factory()->create(['customer_id' => $this->cliente->id]);
    $oferta->servicios()->create([
        'servicio_id' => $this->servicio->id,
        'cantidad' => 1,
        'precio_unitario' => 100,
    ]);
    $oferta->gastos()->create([
        'gasto_id' => $this->gasto->id,
        'cantidad' => 2,
        'precio_unitario' => 50,
    ]);

    $payload = payloadOferta($this->cliente, $this->servicio, null, [
        'estado' => Oferta::ESTADO_ACEPTADA,
        'servicios' => [[
            'servicio_id' => $this->servicio->id,
            'cantidad' => '4',
            'precio_unitario' => '110.00',
        ]],
        'gastos' => [],
    ]);

    $this->get(route('ofertas.edit', $oferta))->assertOk();

    $this->put(route('ofertas.update', $oferta), $payload)
        ->assertRedirect(route('ofertas.show', $oferta))
        ->assertSessionHasNoErrors();

    $oferta->refresh()->load(['servicios', 'gastos']);

    expect($oferta->estado)->toBe(Oferta::ESTADO_ACEPTADA)
        ->and($oferta->servicios)->toHaveCount(1)
        ->and((float) $oferta->servicios->first()->cantidad)->toBe(4.0)
        ->and($oferta->total_servicios)->toBe(440.0)
        ->and($oferta->gastos)->toHaveCount(0)
        ->and($oferta->total)->toBe(440.0);

    $this->assertDatabaseMissing('oferta_gastos', ['oferta_id' => $oferta->id]);
});

test('eliminar una oferta elimina sus líneas de servicios y gastos', function () {
    $this->actingAs($this->operador);

    $oferta = Oferta::factory()->create(['customer_id' => $this->cliente->id]);
    $oferta->servicios()->create([
        'servicio_id' => $this->servicio->id,
        'cantidad' => 1,
        'precio_unitario' => 100,
    ]);
    $oferta->gastos()->create([
        'gasto_id' => $this->gasto->id,
        'cantidad' => 1,
        'precio_unitario' => 50,
    ]);

    $this->delete(route('ofertas.destroy', $oferta))
        ->assertRedirect(route('ofertas.index'));

    $this->assertDatabaseMissing('ofertas', ['id' => $oferta->id]);
    $this->assertDatabaseMissing('oferta_servicios', ['oferta_id' => $oferta->id]);
    $this->assertDatabaseMissing('oferta_gastos', ['oferta_id' => $oferta->id]);
});

test('el detalle de la oferta muestra los totales calculados', function () {
    $this->actingAs($this->operador);

    $oferta = Oferta::factory()->create([
        'customer_id' => $this->cliente->id,
        'codigo' => 'OF-000050',
    ]);
    $oferta->servicios()->create([
        'servicio_id' => $this->servicio->id,
        'cantidad' => 2,
        'precio_unitario' => 100,
    ]);
    $oferta->gastos()->create([
        'gasto_id' => $this->gasto->id,
        'cantidad' => 3,
        'precio_unitario' => 50,
    ]);

    $this->get(route('ofertas.show', $oferta))
        ->assertOk()
        ->assertSee('OF-000050')
        ->assertSee('350,00');
});

test('un administrador puede crear, actualizar y eliminar servicios y gastos', function () {
    $this->actingAs($this->admin);

    $this->post(route('servicios.store'), [
        'nombre' => 'Soporte técnico',
        'unidad_medida_id' => $this->unidad->id,
        'precio_unitario' => '75.50',
        'activo' => '1',
    ])->assertRedirect(route('servicios.index'))->assertSessionHasNoErrors();

    $servicio = Servicio::query()->where('nombre', 'Soporte técnico')->firstOrFail();

    expect((float) $servicio->precio_unitario)->toBe(75.5)
        ->and($servicio->activo)->toBeTrue();

    $this->get(route('servicios.edit', $servicio))->assertOk();

    $this->put(route('servicios.update', $servicio), [
        'nombre' => 'Soporte técnico',
        'unidad_medida_id' => $this->unidad->id,
        'precio_unitario' => '80',
        'activo' => '0',
    ])->assertRedirect(route('servicios.index'))->assertSessionHasNoErrors();

    expect((float) $servicio->fresh()->precio_unitario)->toBe(80.0)
        ->and($servicio->fresh()->activo)->toBeFalse();

    $this->post(route('gastos.store'), [
        'nombre' => 'Flete',
        'precio_unitario' => '30',
        'activo' => '1',
    ])->assertRedirect(route('gastos.index'))->assertSessionHasNoErrors();

    $gasto = Gasto::query()->where('nombre', 'Flete')->firstOrFail();

    expect((float) $gasto->precio_unitario)->toBe(30.0)
        ->and($gasto->activo)->toBeTrue();

    $this->get(route('gastos.edit', $gasto))->assertOk();

    $this->delete(route('gastos.destroy', $gasto))
        ->assertRedirect(route('gastos.index'));

    $this->assertDatabaseMissing('gastos', ['id' => $gasto->id]);
});

test('los catálogos validan nombre y precio unitario', function () {
    $this->actingAs($this->admin);

    $this->post(route('servicios.store'), [
        'nombre' => '',
        'unidad_medida_id' => null,
        'precio_unitario' => '-1',
    ])->assertSessionHasErrors(['nombre', 'unidad_medida_id', 'precio_unitario']);

    $this->post(route('gastos.store'), [
        'nombre' => '',
        'precio_unitario' => '-1',
    ])->assertSessionHasErrors(['nombre', 'precio_unitario']);
});

test('no se pueden eliminar servicios o gastos asociados a una oferta', function () {
    $oferta = Oferta::factory()->create(['customer_id' => $this->cliente->id]);
    $oferta->servicios()->create([
        'servicio_id' => $this->servicio->id,
        'cantidad' => 1,
        'precio_unitario' => 100,
    ]);
    $oferta->gastos()->create([
        'gasto_id' => $this->gasto->id,
        'cantidad' => 1,
        'precio_unitario' => 50,
    ]);

    $this->actingAs($this->admin);

    $this->delete(route('servicios.destroy', $this->servicio))
        ->assertSessionHas('error');
    $this->delete(route('gastos.destroy', $this->gasto))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('servicios', ['id' => $this->servicio->id]);
    $this->assertDatabaseHas('gastos', ['id' => $this->gasto->id]);
});

test('el listado de ofertas permite filtrar por estado', function () {
    Oferta::factory()->create([
        'customer_id' => $this->cliente->id,
        'codigo' => 'OF-000010',
        'estado' => Oferta::ESTADO_BORRADOR,
    ]);
    Oferta::factory()->create([
        'customer_id' => $this->cliente->id,
        'codigo' => 'OF-000011',
        'estado' => Oferta::ESTADO_ACEPTADA,
    ]);

    $this->actingAs($this->operador)
        ->get(route('ofertas.index', ['estado' => Oferta::ESTADO_ACEPTADA]))
        ->assertOk()
        ->assertSee('OF-000011')
        ->assertDontSee('OF-000010');
});
