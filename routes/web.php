<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductLandingController;

Route::get('/', [ProductLandingController::class, 'index'])->name('product.landing');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

