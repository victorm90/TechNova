<?php

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('customers can be created, updated, viewed and deleted', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('customers.index'))
        ->assertOk()
        ->assertViewIs('customers.index');

    $this->post(route('customers.store'), [
        'name' => 'Ana Maria Perez',
        'address' => 'Calle Principal 123',
        'phone' => '55510000',
        'identity_card' => '11223344556',
    ])->assertRedirect(route('customers.index'));

    $customer = Customer::query()->firstOrFail();

    $this->get(route('customers.show', $customer))
        ->assertOk()
        ->assertSee('Ana Maria Perez');

    $this->put(route('customers.update', $customer), [
        'name' => 'Ana Maria Perez',
        'address' => 'Avenida Central 456',
        'phone' => '55510001',
        'identity_card' => '11223344556',
    ])->assertRedirect(route('customers.index'));

    expect($customer->refresh()->name)->toBe('Ana Maria Perez');

    $this->delete(route('customers.destroy', $customer))
        ->assertRedirect(route('customers.index'));

    expect(Customer::query()->count())->toBe(0);
});

test('customer identity cards must be unique', function () {
    $this->actingAs(User::factory()->create());

    $customer = Customer::factory()->create(['identity_card' => '11223344556']);

    $this->from(route('customers.create'))
        ->post(route('customers.store'), [
            'name' => 'Otro Cliente',
            'address' => 'Otra direccion',
            'phone' => '55520000',
            'identity_card' => $customer->identity_card,
        ])
        ->assertSessionHasErrors('identity_card');
});

test('customer names and phones must be unique', function () {
    $this->actingAs(User::factory()->create());

    $customer = Customer::factory()->create([
        'name' => 'Cliente Registrado',
        'phone' => '55550000',
    ]);

    $this->from(route('customers.index'))
        ->post(route('customers.store'), [
            'name' => $customer->name,
            'address' => 'Otra direccion valida',
            'phone' => $customer->phone,
            'identity_card' => '12345678901',
        ])
        ->assertSessionHasErrors(['name', 'phone']);
});

test('customer fields enforce their required formats and lengths', function () {
    $this->actingAs(User::factory()->create());

    $response = $this->from(route('customers.index'))
        ->post(route('customers.store'), [
            'name' => 'Corto',
            'address' => str_repeat('A', 101),
            'phone' => '1234ABCD',
            'identity_card' => '1234567890',
        ]);

    $response->assertRedirect(route('customers.index'))
        ->assertSessionHasErrors(['name', 'address', 'phone', 'identity_card']);

    expect(Customer::query()->count())->toBe(0);
});
