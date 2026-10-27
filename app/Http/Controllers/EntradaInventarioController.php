<?php

namespace App\Http\Controllers;

use App\Http\Requests\Inventario\StoreEntradaRequest;
use App\Models\Almacen;
use App\Models\OperacionInventario;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Services\InventarioService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class EntradaInventarioController extends Controller
{
    public function __construct(private readonly InventarioService $inventario) {}

    /**
     * Historial de entradas de inventario.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->limit(100)->toString();

        $operaciones = OperacionInventario::query()
            ->where('tipo', OperacionInventario::TIPO_ENTRADA)
            ->with(['almacen', 'proveedor', 'user'])
            ->withCount('lineas')
            ->withSum('lineas as cantidad_total', 'cantidad')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('codigo', 'like', "%{$search}%")
                        ->orWhere('referencia', 'like', "%{$search}%")
                        ->orWhereHas('proveedor', fn (Builder $query): Builder => $query->where('nombre', 'like', "%{$search}%"));
                });
            })
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('inventario.operaciones.index', [
            'operaciones' => $operaciones,
            'search' => $search,
            'tipo' => OperacionInventario::TIPO_ENTRADA,
        ]);
    }

    /**
     * Formulario para registrar una entrada con múltiples líneas.
     */
    public function create(): View
    {
        return view('inventario.operaciones.create', [
            'tipo' => OperacionInventario::TIPO_ENTRADA,
            'almacenes' => Almacen::query()->activos()->orderBy('nombre')->get(),
            'productos' => $this->productosParaOperacion(),
            'proveedores' => Proveedor::query()->orderBy('nombre')->get(['id', 'nombre', 'activo']),
        ]);
    }

    /**
     * Registrar una entrada de inventario.
     */
    public function store(StoreEntradaRequest $request): RedirectResponse
    {
        $datos = $request->validated();

        try {
            $operacion = DB::transaction(function () use ($datos, $request): OperacionInventario {
                $proveedorId = $datos['proveedor_id'] ?? null;
                $nombreProveedor = trim((string) ($datos['proveedor_nombre'] ?? ''));

                if ($nombreProveedor !== '') {
                    $proveedor = Proveedor::query()->firstOrCreate(
                        ['nombre' => $nombreProveedor],
                        ['activo' => true],
                    );

                    $proveedorId = $proveedor->id;
                }

                return $this->inventario->registrarEntrada([
                    'almacen_id' => $datos['almacen_id'],
                    'proveedor_id' => $proveedorId,
                    'referencia' => $datos['referencia'] ?? null,
                    'notas' => $datos['notas'] ?? null,
                    'lineas' => $datos['lineas'],
                ], $request->user());
            });

            return redirect()
                ->route('entradas.show', $operacion)
                ->with('success', "Entrada {$operacion->codigo} registrada correctamente.");
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Error al registrar entrada de inventario.', [
                'exception' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->with('error', 'No se pudo registrar la entrada. Inténtelo nuevamente.');
        }
    }

    /**
     * Detalle de una entrada.
     */
    public function show(OperacionInventario $operacion): View
    {
        abort_unless($operacion->tipo === OperacionInventario::TIPO_ENTRADA, 404);

        $operacion->load(['almacen', 'proveedor', 'user', 'lineas.producto.unidadMedida']);

        return view('inventario.operaciones.show', [
            'operacion' => $operacion,
            'tipo' => OperacionInventario::TIPO_ENTRADA,
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Producto>
     */
    private function productosParaOperacion(): \Illuminate\Database\Eloquent\Collection
    {
        return Producto::query()
            ->activos()
            ->with(['categoriaProducto', 'unidadMedida'])
            ->orderBy('nombre')
            ->get();
    }
}
