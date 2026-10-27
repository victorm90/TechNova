<?php

namespace App\Http\Controllers;

use App\Http\Requests\Producto\StoreProductoRequest;
use App\Http\Requests\Producto\UpdateProductoRequest;
use App\Models\Almacen;
use App\Models\CategoriaProducto;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\UnidadMedida;
use App\Services\ProductoService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class ProductoController extends Controller
{
    public function __construct(private readonly ProductoService $productos) {}

    /**
     * Listado de productos con existencias consolidadas.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->limit(100)->toString();
        $categoriaId = $request->integer('categoria');

        $productos = Producto::query()
            ->with(['categoriaProducto', 'unidadMedida'])
            ->withSum('inventarios as stock_total', 'cantidad')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('nombre', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('descripcion', 'like', "%{$search}%");
                });
            })
            ->when($categoriaId > 0, fn (Builder $query): Builder => $query->where('categoria_producto_id', $categoriaId))
            ->orderBy('nombre')
            ->paginate(12)
            ->withQueryString();

        $categorias = CategoriaProducto::query()->orderBy('nombre')->get(['id', 'nombre']);

        $resumen = [
            'total' => Producto::query()->count(),
            'activos' => Producto::query()->where('activo', true)->count(),
            'bajo_minimo' => Producto::query()
                ->whereRaw('(SELECT COALESCE(SUM(cantidad), 0) FROM inventarios WHERE inventarios.producto_id = productos.id) <= productos.stock_minimo')
                ->count(),
            'valor' => (float) Inventario::query()
                ->selectRaw('COALESCE(SUM(cantidad * costo_promedio), 0) as valor')
                ->value('valor'),
        ];

        return view('productos.index', compact('productos', 'categorias', 'search', 'categoriaId', 'resumen'));
    }

    /**
     * Formulario de creación de producto.
     */
    public function create(): View
    {
        return view('productos.form', [
            'producto' => new Producto([
                'activo' => true,
                'precio_compra' => 0,
                'precio_venta' => 0,
                'stock_minimo' => 0,
            ]),
            'categorias' => CategoriaProducto::query()->activas()->orderBy('nombre')->get(),
            'unidades' => UnidadMedida::query()->orderBy('nombre')->get(),
            'almacenes' => Almacen::query()->activos()->orderBy('nombre')->get(),
        ]);
    }

    /**
     * Guardar un producto y su inventario inicial.
     */
    public function store(StoreProductoRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $producto = $this->productos->crear($validated, $request->user());

            return redirect()
                ->route('productos.show', $producto)
                ->with('success', "Producto {$producto->sku} creado correctamente.");
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Error al crear producto.', [
                'exception' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->with('error', 'No se pudo crear el producto. Inténtelo nuevamente.');
        }
    }

    /**
     * Detalle del producto con inventario y movimientos recientes.
     */
    public function show(Producto $producto): View
    {
        $producto->load(['categoriaProducto', 'unidadMedida', 'inventarios.almacen']);

        $movimientos = $producto->movimientos()
            ->with(['almacen', 'operacion', 'user'])
            ->latest('id')
            ->limit(20)
            ->get();

        return view('productos.show', [
            'producto' => $producto,
            'movimientos' => $movimientos,
            'stockTotal' => $producto->stockTotal(),
        ]);
    }

    /**
     * Formulario de edición de producto.
     */
    public function edit(Producto $producto): View
    {
        return view('productos.form', [
            'producto' => $producto,
            'categorias' => CategoriaProducto::query()->orderBy('nombre')->get(),
            'unidades' => UnidadMedida::query()->orderBy('nombre')->get(),
            'almacenes' => Almacen::query()->activos()->orderBy('nombre')->get(),
        ]);
    }

    /**
     * Actualizar los datos maestros del producto.
     */
    public function update(UpdateProductoRequest $request, Producto $producto): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $this->productos->actualizar($producto, $validated);

            return redirect()
                ->route('productos.show', $producto)
                ->with('success', 'Producto actualizado correctamente.');
        } catch (Throwable $e) {
            Log::error('Error al actualizar producto.', [
                'producto_id' => $producto->id,
                'exception' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->with('error', 'No se pudo actualizar el producto. Inténtelo nuevamente.');
        }
    }

    /**
     * Eliminar un producto sin historial de inventario.
     */
    public function destroy(Producto $producto): RedirectResponse
    {
        if ($producto->lineasOperacion()->exists() || $producto->stockTotal() > 0) {
            return back()->with(
                'error',
                'No se puede eliminar el producto porque tiene movimientos o existencias. Desactívalo en su lugar.',
            );
        }

        try {
            DB::transaction(fn () => $producto->delete());

            return redirect()
                ->route('productos.index')
                ->with('success', 'Producto eliminado correctamente.');
        } catch (Throwable $e) {
            Log::error('Error al eliminar producto.', [
                'producto_id' => $producto->id,
                'exception' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'No se pudo eliminar el producto. Inténtelo nuevamente.');
        }
    }
}
