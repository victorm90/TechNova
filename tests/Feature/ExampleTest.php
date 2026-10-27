<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the product landing is the home page', function () {
    $response = $this->get('/');

    $response
        ->assertOk()
        ->assertViewIs('admin.product-landing');
});

test('the product landing links to the dashboard', function () {
    $this->actingAs(User::factory()->admin()->create());

    $response = $this->get('/dashboard');

    $response
        ->assertOk()
        ->assertViewIs('admin.dashboard');
});
