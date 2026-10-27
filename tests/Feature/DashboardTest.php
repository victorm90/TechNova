<?php

use App\Models\Customer;
use App\Models\Municipio;
use App\Models\Provincia;
use App\Models\Servicio;
use App\Models\UnidadMedida;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the dashboard shows the real system metrics', function () {
    $this->actingAs(User::factory()->admin()->create());

    $provincia = Provincia::query()->create([
        'nombre' => 'La Habana',
        'codigo' => '01',
    ]);

    Municipio::query()->create([
        'nombre' => 'Playa',
        'codigo' => '011',
        'provincia_id' => $provincia->id,
    ]);

    $unidad = UnidadMedida::query()->create([
        'nombre' => 'Hora',
        'abreviatura' => 'h',
    ]);

    Servicio::query()->create([
        'nombre' => 'Servicio activo',
        'unidad_medida_id' => $unidad->id,
        'activo' => true,
    ]);

    Servicio::query()->create([
        'nombre' => 'Servicio inactivo',
        'unidad_medida_id' => $unidad->id,
        'activo' => false,
    ]);

    Customer::factory()->count(3)->create();
    Customer::factory()->create(['name' => 'Cliente Reciente']);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertViewIs('admin.dashboard')
        ->assertViewHas('mock', fn (array $mock) => count($mock['kpis']) === 4)
        ->assertSee('Valor del inventario')
        ->assertSee('Datos de demostración')
        ->assertSee('Cliente Reciente');
});

test('the dashboard renders when the system has no records', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Sin clientes registrados');
});

test('the services page lists the services with their unit of measure', function () {
    $this->actingAs(User::factory()->admin()->create());

    $unidad = UnidadMedida::query()->create([
        'nombre' => 'Hora',
        'abreviatura' => 'h',
    ]);

    Servicio::query()->create([
        'nombre' => 'Mantenimiento general',
        'unidad_medida_id' => $unidad->id,
        'activo' => true,
    ]);

    $this->get(route('servicios.index'))
        ->assertOk()
        ->assertViewIs('nomencladores.index')
        ->assertSee('Mantenimiento general')
        ->assertSee('Hora')
        ->assertSee('Activo');
});
