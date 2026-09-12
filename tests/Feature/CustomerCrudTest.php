<?php

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('customers can be created, updated, viewed and deleted', function () {
    $this->get(route('customers.index'))
        ->assertOk()
        ->assertViewIs('customers.index');

    $this->post(route('customers.store'), [
        'name' => 'Ana Perez',
        'address' => 'Calle Principal 123',
        'phone' => '555-1000',
        'identity_card' => '11223344556',
    ])->assertRedirect(route('customers.index'));

    $customer = Customer::query()->firstOrFail();

    $this->get(route('customers.show', $customer))
        ->assertOk()
        ->assertSee('Ana Perez');

    $this->put(route('customers.update', $customer), [
        'name' => 'Ana Maria Perez',
        'address' => 'Avenida Central 456',
        'phone' => '555-1001',
        'identity_card' => '11223344556',
    ])->assertRedirect(route('customers.index'));

    expect($customer->refresh()->name)->toBe('Ana Maria Perez');

    $this->delete(route('customers.destroy', $customer))
        ->assertRedirect(route('customers.index'));

    expect(Customer::query()->count())->toBe(0);
});

test('customer identity cards must be unique', function () {
    $customer = Customer::factory()->create(['identity_card' => '11223344556']);

    $this->from(route('customers.create'))
        ->post(route('customers.store'), [
            'name' => 'Otro Cliente',
            'address' => 'Otra direccion',
            'phone' => '555-2000',
            'identity_card' => $customer->identity_card,
        ])
        ->assertSessionHasErrors('identity_card');
});
