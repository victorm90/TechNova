<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use App\Models\Inventario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventarioController extends Controller
{
    /**
     * Existencias consolidadas por producto y almacén.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->limit(100)->toString();
        $almacenId = $request->integer('almacen');
        $soloBajo = $request->boolean('bajo');

        $base = Inventario::query()
            ->with(['producto.categoriaProducto', 'producto.unidadMedida', 'almacen'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->whereHas('producto', function (Builder $query) use ($search): void {
                    $query->where(function (Builder $query) use ($search): void {
                        $query->where('nombre', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%");
                    });
                });
            })
            ->when($almacenId > 0, fn (Builder $query): Builder => $query->where('almacen_id', $almacenId))
            ->when($soloBajo, fn (Builder $query): Builder => $query->whereHas(
                'producto',
                fn (Builder $query): Builder => $query->whereColumn(
                    'productos.stock_minimo',
                    '>=',
                    'inventarios.cantidad',
                ),
            ));

        $inventarios = (clone $base)
            ->orderBy('producto_id')
            ->orderBy('almacen_id')
            ->paginate(15)
            ->withQueryString();

        $totales = (clone $base)
            ->reorder()
            ->selectRaw('COALESCE(SUM(inventarios.cantidad), 0) as unidades, COALESCE(SUM(inventarios.cantidad * inventarios.costo_promedio), 0) as valor')
            ->first();

        $resumen = [
            'unidades' => (float) ($totales->unidades ?? 0),
            'valor' => (float) ($totales->valor ?? 0),
            'bajo_minimo' => Inventario::query()
                ->whereHas('producto', fn (Builder $query): Builder => $query->whereColumn(
                    'productos.stock_minimo',
                    '>=',
                    'inventarios.cantidad',
                ))
                ->count(),
            'almacenes' => Almacen::query()->where('activo', true)->count(),
        ];

        return view('inventario.index', [
            'inventarios' => $inventarios,
            'almacenes' => Almacen::query()->orderBy('nombre')->get(['id', 'nombre']),
            'almacenesActivos' => Almacen::query()->activos()->orderBy('nombre')->get(),
            'resumen' => $resumen,
            'search' => $search,
            'almacenId' => $almacenId,
            'soloBajo' => $soloBajo,
        ]);
    }
}
