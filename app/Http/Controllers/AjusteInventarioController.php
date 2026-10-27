<?php

namespace App\Http\Controllers;

use App\Http\Requests\Inventario\StoreAjusteRequest;
use App\Models\Inventario;
use App\Models\MovimientoInventario;
use App\Models\OperacionInventario;
use App\Models\OperacionInventarioLinea;
use App\Services\InventarioService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class AjusteInventarioController extends Controller
{
    public function __construct(private readonly InventarioService $inventario) {}

    /**
     * Historial de ajustes de inventario.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->limit(100)->toString();

        $operaciones = OperacionInventario::query()
            ->where('tipo', OperacionInventario::TIPO_AJUSTE)
            ->with(['almacen', 'user', 'lineas.producto'])
            ->withCount('lineas')
            ->withSum('lineas as cantidad_total', 'cantidad')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('codigo', 'like', "%{$search}%")
                        ->orWhereHas('lineas.producto', fn (Builder $query): Builder => $query
                            ->where('nombre', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%"));
                });
            })
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('inventario.ajustes.index', [
            'operaciones' => $operaciones,
            'search' => $search,
            'resumen' => [
                'operaciones' => OperacionInventario::query()
                    ->where('tipo', OperacionInventario::TIPO_AJUSTE)
                    ->count(),
                'lineas' => OperacionInventarioLinea::query()
                    ->whereHas('operacion', fn (Builder $query): Builder => $query
                        ->where('tipo', OperacionInventario::TIPO_AJUSTE))
                    ->count(),
                'aumentos' => MovimientoInventario::query()
                    ->where('tipo', OperacionInventario::TIPO_AJUSTE)
                    ->where('cantidad', '>', 0)
                    ->count(),
                'disminuciones' => MovimientoInventario::query()
                    ->where('tipo', OperacionInventario::TIPO_AJUSTE)
                    ->where('cantidad', '<', 0)
                    ->count(),
            ],
        ]);
    }

    /**
     * Formulario para ajustar el stock de un producto existente.
     */
    public function create(Request $request): View
    {
        $inventarios = Inventario::query()
            ->with(['producto', 'almacen'])
            ->whereHas('producto')
            ->get()
            ->sortBy(fn (Inventario $inventario): string => $inventario->producto?->nombre ?? '')
            ->values();

        return view('inventario.ajustes.create', [
            'inventarios' => $inventarios,
            'inventarioId' => $request->integer('inventario'),
        ]);
    }

    /**
     * Registrar un ajuste de inventario.
     */
    public function store(StoreAjusteRequest $request): RedirectResponse
    {
        $datos = $request->validated();

        try {
            $operacion = $this->inventario->registrarAjuste([
                'inventario_id' => $datos['inventario_id'],
                'cantidad' => $datos['cantidad'],
            ], $request->user());

            return redirect()
                ->route('ajustes.show', $operacion)
                ->with('success', "Ajuste {$operacion->codigo} registrado correctamente.");
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Error al registrar ajuste de inventario.', [
                'exception' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->with('error', 'No se pudo registrar el ajuste. Inténtelo nuevamente.');
        }
    }

    /**
     * Detalle de un ajuste de inventario.
     */
    public function show(OperacionInventario $operacion): View
    {
        abort_unless($operacion->tipo === OperacionInventario::TIPO_AJUSTE, 404);

        $operacion->load(['almacen', 'user', 'lineas.producto.unidadMedida', 'movimientos.producto']);

        return view('inventario.ajustes.show', compact('operacion'));
    }
}
