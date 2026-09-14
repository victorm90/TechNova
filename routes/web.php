<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductLandingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProductLandingController::class, 'index'])->name('product.landing');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::resource('clientes', CustomerController::class)
    ->parameters(['clientes' => 'customer'])
    ->names([
        'index' => 'customers.index',
        'create' => 'customers.create',
        'store' => 'customers.store',
        'show' => 'customers.show',
        'edit' => 'customers.edit',
        'update' => 'customers.update',
        'destroy' => 'customers.destroy',
    ]);
