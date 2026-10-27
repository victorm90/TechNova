<?php

namespace App\Http\Controllers;

use App\Http\Requests\Inventario\StoreSalidaRequest;
use App\Models\Almacen;
use App\Models\Inventario;
use App\Models\OperacionInventario;
use App\Models\OperacionInventarioLinea;
use App\Models\Producto;
use App\Services\InventarioService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class SalidaInventarioController extends Controller
{
    public function __construct(private readonly InventarioService $inventario) {}

    /**
     * Historial de salidas de inventario.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->limit(100)->toString();

        $operaciones = OperacionInventario::query()
            ->where('tipo', OperacionInventario::TIPO_SALIDA)
            ->with(['almacen', 'user'])
            ->withCount('lineas')
            ->withSum('lineas as cantidad_total', 'cantidad')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('codigo', 'like', "%{$search}%")
                        ->orWhere('referencia', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('inventario.operaciones.index', [
            'operaciones' => $operaciones,
            'search' => $search,
            'tipo' => OperacionInventario::TIPO_SALIDA,
            'resumen' => [
                'operaciones' => OperacionInventario::query()
                    ->where('tipo', OperacionInventario::TIPO_SALIDA)
                    ->count(),
                'unidades' => (int) OperacionInventarioLinea::query()
                    ->whereHas('operacion', fn (Builder $query): Builder => $query
                        ->where('tipo', OperacionInventario::TIPO_SALIDA))
                    ->sum('cantidad'),
                'valor' => (float) OperacionInventario::query()
                    ->where('tipo', OperacionInventario::TIPO_SALIDA)
                    ->sum('total'),
            ],
        ]);
    }

    /**
     * Formulario para registrar una salida con múltiples líneas.
     */
    public function create(): View
    {
        $stock = Inventario::query()
            ->get(['producto_id', 'almacen_id', 'cantidad', 'costo_promedio'])
            ->mapWithKeys(fn (Inventario $inventario): array => [
                "{$inventario->almacen_id}-{$inventario->producto_id}" => (float) $inventario->cantidad,
            ]);

        $costos = Inventario::query()
            ->get(['producto_id', 'almacen_id', 'costo_promedio'])
            ->mapWithKeys(fn (Inventario $inventario): array => [
                "{$inventario->almacen_id}-{$inventario->producto_id}" => (float) $inventario->costo_promedio,
            ]);

        return view('inventario.operaciones.create', [
            'tipo' => OperacionInventario::TIPO_SALIDA,
            'almacenes' => Almacen::query()->activos()->orderBy('nombre')->get(),
            'productos' => $this->productosParaOperacion(),
            'proveedores' => collect(),
            'stock' => $stock,
            'costos' => $costos,
        ]);
    }

    /**
     * Registrar una salida de inventario.
     */
    public function store(StoreSalidaRequest $request): RedirectResponse
    {
        $datos = $request->validated();

        try {
            $operacion = $this->inventario->registrarSalida([
                'almacen_id' => $datos['almacen_id'],
                'referencia' => $datos['referencia'] ?? null,
                'notas' => $datos['notas'] ?? null,
                'lineas' => $datos['lineas'],
            ], $request->user());

            return redirect()
                ->route('salidas.show', $operacion)
                ->with('success', "Salida {$operacion->codigo} registrada correctamente.");
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Error al registrar salida de inventario.', [
                'exception' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->with('error', 'No se pudo registrar la salida. Inténtelo nuevamente.');
        }
    }

    /**
     * Detalle de una salida.
     */
    public function show(OperacionInventario $operacion): View
    {
        abort_unless($operacion->tipo === OperacionInventario::TIPO_SALIDA, 404);

        $operacion->load(['almacen', 'user', 'lineas.producto.unidadMedida']);

        return view('inventario.operaciones.show', [
            'operacion' => $operacion,
            'tipo' => OperacionInventario::TIPO_SALIDA,
        ]);
    }

    /**
     * @return Collection<int, Producto>
     */
    private function productosParaOperacion(): Collection
    {
        return Producto::query()
            ->activos()
            ->with(['categoriaProducto', 'unidadMedida'])
            ->orderBy('nombre')
            ->get();
    }
}
