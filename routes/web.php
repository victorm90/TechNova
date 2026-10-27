<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MunicipioController;
use App\Http\Controllers\ProductLandingController;
use App\Http\Controllers\ProvinciaController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\UnidadMedidaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProductLandingController::class, 'index'])->name('product.landing');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
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

    Route::middleware('can:manage-nomencladores')->group(function () {
        Route::get('/nomencladores/provincias', [ProvinciaController::class, 'index'])
            ->name('provincias.index');
        Route::get('/nomencladores/municipios', [MunicipioController::class, 'index'])
            ->name('municipios.index');
        Route::get('/nomencladores/unidades-medida', [UnidadMedidaController::class, 'index'])
            ->name('unidades-medida.index');
        Route::get('/nomencladores/servicios', [ServicioController::class, 'index'])
            ->name('servicios.index');
    });
});
