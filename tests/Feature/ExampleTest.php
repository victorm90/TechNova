<?php

test('the product landing is the home page', function () {
    $response = $this->get('/');

    $response
        ->assertOk()
        ->assertViewIs('admin.product-landing');
});

test('the product landing links to the dashboard', function () {
    $response = $this->get('/dashboard');

    $response
        ->assertOk()
        ->assertViewIs('admin.dasboard');
});
