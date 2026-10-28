<?php

use App\Http\Controllers\AjusteInventarioController;
use App\Http\Controllers\AlmacenController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CategoriaProductoController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EntradaInventarioController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\GastoController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\MunicipioController;
use App\Http\Controllers\OfertaController;
use App\Http\Controllers\ProductLandingController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ProvinciaController;
use App\Http\Controllers\SalidaInventarioController;
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

    Route::middleware('can:manage-ofertas')->group(function () {
        Route::post('ofertas/rapida', [OfertaController::class, 'storeRapida'])
            ->name('ofertas.storeRapida');

        Route::resource('ofertas', OfertaController::class);

        Route::post('ofertas/{oferta}/servicios', [OfertaController::class, 'storeServicio'])
            ->name('ofertas.servicios.store');
        Route::post('ofertas/{oferta}/servicios/{linea}/productos', [OfertaController::class, 'storeProducto'])
            ->name('ofertas.servicios.productos.store');
        Route::post('ofertas/{oferta}/gastos', [OfertaController::class, 'storeGasto'])
            ->name('ofertas.gastos.store');
        Route::post('ofertas/{oferta}/facturar', [OfertaController::class, 'facturar'])
            ->name('ofertas.facturar');
        Route::get('ofertas/{oferta}/imprimir', [OfertaController::class, 'imprimir'])
            ->name('ofertas.imprimir');
        Route::get('ofertas/{oferta}/pdf', [OfertaController::class, 'pdf'])
            ->name('ofertas.pdf');

        Route::get('facturas/{factura}', [FacturaController::class, 'show'])
            ->name('facturas.show');
        Route::get('facturas/{factura}/imprimir', [FacturaController::class, 'imprimir'])
            ->name('facturas.imprimir');
        Route::get('facturas/{factura}/pdf', [FacturaController::class, 'pdf'])
            ->name('facturas.pdf');
    });

    /*
    |--------------------------------------------------------------------------
    | Inventario y catálogo
    |--------------------------------------------------------------------------
    */

    Route::get('/inventario', [InventarioController::class, 'index'])
        ->middleware('can:ver-inventario')
        ->name('inventario.index');

    Route::get('/productos', [ProductoController::class, 'index'])
        ->middleware('can:ver-inventario')
        ->name('productos.index');

    Route::get('/productos/crear', [ProductoController::class, 'create'])
        ->middleware('can:manage-productos')
        ->name('productos.create');

    Route::post('/productos', [ProductoController::class, 'store'])
        ->middleware('can:manage-productos')
        ->name('productos.store');

    Route::get('/productos/{producto}/editar', [ProductoController::class, 'edit'])
        ->middleware('can:manage-productos')
        ->name('productos.edit');

    Route::put('/productos/{producto}', [ProductoController::class, 'update'])
        ->middleware('can:manage-productos')
        ->name('productos.update');

    Route::delete('/productos/{producto}', [ProductoController::class, 'destroy'])
        ->middleware('can:manage-productos')
        ->name('productos.destroy');

    Route::get('/productos/{producto}', [ProductoController::class, 'show'])
        ->middleware('can:ver-inventario')
        ->name('productos.show');

    Route::middleware('can:manage-productos')->group(function () {
        Route::resource('categorias', CategoriaProductoController::class)
            ->parameters(['categorias' => 'categoria'])
            ->except('show')
            ->names([
                'index' => 'categorias.index',
                'create' => 'categorias.create',
                'store' => 'categorias.store',
                'edit' => 'categorias.edit',
                'update' => 'categorias.update',
                'destroy' => 'categorias.destroy',
            ]);

        Route::resource('almacenes', AlmacenController::class)
            ->parameters(['almacenes' => 'almacen'])
            ->except('show')
            ->names([
                'index' => 'almacenes.index',
                'create' => 'almacenes.create',
                'store' => 'almacenes.store',
                'edit' => 'almacenes.edit',
                'update' => 'almacenes.update',
                'destroy' => 'almacenes.destroy',
            ]);
    });

    Route::middleware('can:manage-proveedores')->group(function () {
        Route::resource('proveedores', ProveedorController::class)
            ->parameters(['proveedores' => 'proveedor'])
            ->except('show')
            ->names([
                'index' => 'proveedores.index',
                'create' => 'proveedores.create',
                'store' => 'proveedores.store',
                'edit' => 'proveedores.edit',
                'update' => 'proveedores.update',
                'destroy' => 'proveedores.destroy',
            ]);
    });

    Route::middleware('can:registrar-movimientos')->group(function () {
        Route::get('/inventario/ajustes', [AjusteInventarioController::class, 'index'])
            ->name('ajustes.index');
        Route::get('/inventario/ajustes/crear', [AjusteInventarioController::class, 'create'])
            ->name('ajustes.create');
        Route::post('/inventario/ajustes', [AjusteInventarioController::class, 'store'])
            ->name('ajustes.store');
        Route::get('/inventario/ajustes/{operacion}', [AjusteInventarioController::class, 'show'])
            ->name('ajustes.show');

        Route::post('/inventario/entradas/almacenes', [AlmacenController::class, 'storeRapido'])
            ->name('entradas.almacenes.store');
        Route::post('/inventario/entradas/proveedores', [ProveedorController::class, 'storeRapido'])
            ->name('entradas.proveedores.store');

        Route::get('/inventario/entradas', [EntradaInventarioController::class, 'index'])
            ->name('entradas.index');
        Route::get('/inventario/entradas/crear', [EntradaInventarioController::class, 'create'])
            ->name('entradas.create');
        Route::post('/inventario/entradas', [EntradaInventarioController::class, 'store'])
            ->name('entradas.store');
        Route::get('/inventario/entradas/{operacion}', [EntradaInventarioController::class, 'show'])
            ->name('entradas.show');

        Route::get('/inventario/salidas', [SalidaInventarioController::class, 'index'])
            ->name('salidas.index');
        Route::get('/inventario/salidas/crear', [SalidaInventarioController::class, 'create'])
            ->name('salidas.create');
        Route::post('/inventario/salidas', [SalidaInventarioController::class, 'store'])
            ->name('salidas.store');
        Route::get('/inventario/salidas/{operacion}', [SalidaInventarioController::class, 'show'])
            ->name('salidas.show');
    });

    Route::middleware('can:manage-nomencladores')->group(function () {
        Route::get('/nomencladores/provincias', [ProvinciaController::class, 'index'])
            ->name('provincias.index');
        Route::get('/nomencladores/municipios', [MunicipioController::class, 'index'])
            ->name('municipios.index');
        Route::get('/nomencladores/unidades-medida', [UnidadMedidaController::class, 'index'])
            ->name('unidades-medida.index');

        Route::resource('servicios', ServicioController::class)
            ->except('show');

        Route::resource('gastos', GastoController::class)
            ->except('show');
    });
});
