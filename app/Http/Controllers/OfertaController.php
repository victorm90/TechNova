<?php

namespace App\Http\Controllers;

use App\Http\Requests\Oferta\FacturarOfertaRequest;
use App\Http\Requests\Oferta\StoreOfertaGastoRequest;
use App\Http\Requests\Oferta\StoreOfertaProductoRequest;
use App\Http\Requests\Oferta\StoreOfertaRapidaRequest;
use App\Http\Requests\Oferta\StoreOfertaRequest;
use App\Http\Requests\Oferta\StoreOfertaServicioRequest;
use App\Http\Requests\Oferta\UpdateOfertaRequest;
use App\Models\Almacen;
use App\Models\Customer;
use App\Models\Gasto;
use App\Models\HistorialPrecio;
use App\Models\Inventario;
use App\Models\Oferta;
use App\Models\OfertaGasto;
use App\Models\OfertaServicio;
use App\Models\OfertaServicioProducto;
use App\Models\Producto;
use App\Models\Servicio;
use App\Services\DocumentoService;
use App\Services\FacturacionService;
use App\Services\OfertaService;
use App\Services\PdfService;
use App\Services\StockOfertaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class OfertaController extends Controller
{
    public function __construct(
        private readonly OfertaService $ofertas,
        private readonly FacturacionService $facturacion,
        private readonly StockOfertaService $stock,
        private readonly DocumentoService $documentos,
        private readonly PdfService $pdf,
    ) {}

    /**
     * Listado de ofertas.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->limit(100)->toString();
        $estado = $request->string('estado')->trim()->toString();

        $ofertas = Oferta::query()
            ->with(['customer', 'servicios.productos', 'gastos', 'factura'])
            ->withCount(['servicios', 'gastos'])
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('codigo', 'like', "%{$search}%")
                        ->orWhereHas('customer', function (Builder $customerQuery) use ($search) {
                            $customerQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when(
                array_key_exists($estado, Oferta::estados()),
                fn (Builder $query) => $query->where('estado', $estado),
            )
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('ofertas.index', [
            'ofertas' => $ofertas,
            'search' => $search,
            'estado' => $estado,
            'clientes' => Customer::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Formulario de creación de oferta.
     */
    public function create(): View
    {
        $oferta = new Oferta([
            'fecha' => now()->toDateString(),
            'estado' => Oferta::ESTADO_BORRADOR,
            'vigencia_dias' => (int) config('empresa.vigencia_dias', 15),
            'almacen_id' => $this->stock->almacenPredeterminadoId(),
        ]);

        return view('ofertas.form', $this->datosFormulario($oferta));
    }

    /**
     * Crear una oferta rápida desde el modal del listado.
     */
    public function storeRapida(StoreOfertaRapidaRequest $request): RedirectResponse
    {
        $datos = $request->validated();

        try {
            $customer = isset($datos['customer_id'])
                ? Customer::query()->findOrFail($datos['customer_id'])
                : Customer::query()->firstOrCreate(
                    ['name' => $datos['cliente_nombre']],
                    ['address' => null, 'phone' => null, 'identity_card' => null],
                );

            $oferta = $this->ofertas->crearBorrador(
                $customer,
                isset($datos['almacen_id']) ? (int) $datos['almacen_id'] : null,
                $datos['fecha'] ?? null,
            );

            return redirect()
                ->route('ofertas.edit', $oferta)
                ->with('success', 'Oferta '.$oferta->codigo.' creada. Complete los servicios, gastos y datos del documento.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Error al crear oferta rápida.', [
                'exception' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->with('error', 'No se pudo crear la oferta. Inténtelo nuevamente.');
        }
    }

    /**
     * Guardar una oferta con sus líneas.
     */
    public function store(StoreOfertaRequest $request): RedirectResponse
    {
        try {
            $oferta = $this->ofertas->crear($request->validated(), $request->user());

            return redirect()
                ->route('ofertas.show', $oferta)
                ->with('success', 'Oferta creada correctamente.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Error al crear oferta.', [
                'exception' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->with('error', 'No se pudo crear la oferta. Inténtelo nuevamente.');
        }
    }

    /**
     * Detalle de una oferta.
     */
    public function show(Oferta $oferta): View
    {
        $oferta->load([
            'customer',
            'almacen',
            'factura',
            'servicios.servicio.unidadMedida',
            'servicios.productos.producto.unidadMedida',
            'gastos.gasto',
        ]);

        $almacenId = $oferta->almacen_id ?? $this->stock->almacenPredeterminadoId();
        $productosDisponibles = $this->productosDisponibles($oferta);

        return view('ofertas.show', [
            'oferta' => $oferta,
            'serviciosDisponibles' => $this->serviciosDisponibles($oferta),
            'gastosDisponibles' => $this->gastosDisponibles($oferta),
            'productosDisponibles' => $productosDisponibles,
            'almacenes' => Almacen::query()->orderBy('nombre')->get(['id', 'nombre', 'activo']),
            'almacenActual' => $almacenId,
            'stockPorProducto' => $almacenId
                ? $this->stock->existencias($almacenId)
                : collect(),
            'stockPorAlmacen' => $this->stockPorAlmacen($productosDisponibles),
            'historiales' => $this->historiales($oferta),
        ]);
    }

    /**
     * Formulario de edición de oferta.
     */
    public function edit(Oferta $oferta): View|RedirectResponse
    {
        if ($oferta->estaFacturada()) {
            return redirect()
                ->route('ofertas.show', $oferta)
                ->with('error', 'La oferta ya fue facturada y no puede modificarse.');
        }

        $oferta->load(['servicios.productos', 'gastos']);

        return view('ofertas.form', $this->datosFormulario($oferta));
    }

    /**
     * Actualizar una oferta y sus líneas.
     */
    public function update(UpdateOfertaRequest $request, Oferta $oferta): RedirectResponse
    {
        try {
            $this->ofertas->actualizar($oferta, $request->validated(), $request->user());

            return redirect()
                ->route('ofertas.show', $oferta)
                ->with('success', 'Oferta actualizada correctamente.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Error al actualizar oferta.', [
                'oferta_id' => $oferta->id,
                'exception' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->with('error', 'No se pudo actualizar la oferta. Inténtelo nuevamente.');
        }
    }

    /**
     * Añadir un servicio desde el modal del detalle.
     */
    public function storeServicio(StoreOfertaServicioRequest $request, Oferta $oferta): RedirectResponse
    {
        try {
            $this->ofertas->agregarServicio($oferta, $request->validated(), $request->user());

            return back()->with('success', 'Servicio agregado a la oferta.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Error al agregar servicio a oferta.', [
                'oferta_id' => $oferta->id,
                'exception' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return back()->with('error', 'No se pudo agregar el servicio.');
        }
    }

    /**
     * Añadir un gasto desde el modal del detalle.
     */
    public function storeGasto(StoreOfertaGastoRequest $request, Oferta $oferta): RedirectResponse
    {
        try {
            $this->ofertas->agregarGasto($oferta, $request->validated(), $request->user());

            return back()->with('success', 'Gasto agregado a la oferta.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Error al agregar gasto a oferta.', [
                'oferta_id' => $oferta->id,
                'exception' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return back()->with('error', 'No se pudo agregar el gasto.');
        }
    }

    /**
     * Añadir un producto de almacén a una línea de servicio.
     */
    public function storeProducto(
        StoreOfertaProductoRequest $request,
        Oferta $oferta,
        OfertaServicio $linea,
    ): RedirectResponse {
        abort_unless((int) $linea->oferta_id === (int) $oferta->id, 404);

        try {
            $this->ofertas->agregarProducto($oferta, $linea, $request->validated(), $request->user());

            return back()->with('success', 'Producto agregado al servicio.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Error al agregar producto a oferta.', [
                'oferta_id' => $oferta->id,
                'oferta_servicio_id' => $linea->id,
                'exception' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return back()->with('error', 'No se pudo agregar el producto.');
        }
    }

    /**
     * Facturar la oferta con los ajustes reales confirmados.
     */
    public function facturar(FacturarOfertaRequest $request, Oferta $oferta): RedirectResponse
    {
        try {
            $factura = $this->facturacion->facturar($oferta, $request->validated(), $request->user());

            return redirect()
                ->route('facturas.show', $factura)
                ->with('success', 'Oferta facturada. Se descontó el stock confirmado y se generó la factura '.$factura->codigo.'.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Error al facturar oferta.', [
                'oferta_id' => $oferta->id,
                'exception' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return back()->with('error', 'No se pudo facturar la oferta. Inténtelo nuevamente.');
        }
    }

    /**
     * Vista imprimible de la oferta (abre el diálogo de impresión).
     */
    public function imprimir(Oferta $oferta): View
    {
        return view('documentos.print', [
            'documento' => $this->documentos->oferta($oferta),
        ]);
    }

    /**
     * Descargar la oferta en PDF.
     */
    public function pdf(Oferta $oferta): Response
    {
        return $this->pdf->descargar(
            'documentos.pdf',
            ['documento' => $this->documentos->oferta($oferta)],
            'Oferta-'.$oferta->codigo.'.pdf',
        );
    }

    /**
     * Eliminar una oferta y sus líneas.
     */
    public function destroy(Oferta $oferta): RedirectResponse
    {
        if ($oferta->estaFacturada()) {
            return back()->with('error', 'No se puede eliminar una oferta facturada.');
        }

        try {
            DB::transaction(fn () => $oferta->delete());

            return redirect()
                ->route('ofertas.index')
                ->with('success', 'Oferta eliminada correctamente.');
        } catch (Throwable $e) {
            Log::error('Error al eliminar oferta.', [
                'oferta_id' => $oferta->id,
                'exception' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'No se pudo eliminar la oferta. Inténtelo nuevamente.');
        }
    }

    /**
     * Datos compartidos por los formularios de alta y edición.
     *
     * @return array<string, mixed>
     */
    private function datosFormulario(Oferta $oferta): array
    {
        $almacenId = old('almacen_id', $oferta->almacen_id ?? $this->stock->almacenPredeterminadoId());
        $productos = $this->productosDisponibles($oferta);

        return [
            'oferta' => $oferta,
            'clientes' => Customer::query()->orderBy('name')->get(),
            'servicios' => $this->serviciosDisponibles($oferta),
            'gastos' => $this->gastosDisponibles($oferta),
            'productos' => $productos,
            'almacenes' => Almacen::query()->orderBy('nombre')->get(['id', 'nombre', 'activo']),
            'almacenActual' => $almacenId,
            'stockPorProducto' => $almacenId
                ? $this->stock->existencias((int) $almacenId)
                : collect(),
            'stockPorAlmacen' => $this->stockPorAlmacen($productos),
        ];
    }

    /**
     * Existencias de cada producto agrupadas por almacén.
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, Producto>  $productos
     * @return Collection<int, Collection<int, int>>
     */
    private function stockPorAlmacen($productos)
    {
        if ($productos->isEmpty()) {
            return collect();
        }

        return Inventario::query()
            ->whereIn('producto_id', $productos->pluck('id'))
            ->get(['producto_id', 'almacen_id', 'cantidad'])
            ->groupBy('producto_id')
            ->map(fn ($existencias) => $existencias->pluck('cantidad', 'almacen_id'));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Servicio>
     */
    private function serviciosDisponibles(Oferta $oferta)
    {
        $usados = $oferta->exists ? $oferta->servicios->pluck('servicio_id') : collect();

        return Servicio::query()
            ->where(fn (Builder $query) => $query->where('activo', true)->orWhereIn('id', $usados))
            ->with('unidadMedida')
            ->orderBy('nombre')
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Gasto>
     */
    private function gastosDisponibles(Oferta $oferta)
    {
        $usados = $oferta->exists ? $oferta->gastos->pluck('gasto_id') : collect();

        return Gasto::query()
            ->where(fn (Builder $query) => $query->where('activo', true)->orWhereIn('id', $usados))
            ->orderBy('nombre')
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Producto>
     */
    private function productosDisponibles(Oferta $oferta)
    {
        $usados = $oferta->exists
            ? $oferta->servicios->flatMap(fn (OfertaServicio $linea) => $linea->productos)->pluck('producto_id')
            : collect();

        return Producto::query()
            ->where(fn (Builder $query) => $query->where('activo', true)->orWhereIn('id', $usados))
            ->with('unidadMedida')
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Historial de precios de todas las líneas de la oferta.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, HistorialPrecio>
     */
    private function historiales(Oferta $oferta)
    {
        $servicioIds = $oferta->servicios->pluck('id');
        $productoIds = $oferta->servicios->flatMap(fn (OfertaServicio $linea) => $linea->productos)->pluck('id');
        $gastoIds = $oferta->gastos->pluck('id');

        if ($servicioIds->isEmpty() && $productoIds->isEmpty() && $gastoIds->isEmpty()) {
            return HistorialPrecio::query()->whereRaw('1 = 0')->get();
        }

        $historiales = HistorialPrecio::query()
            ->with('user')
            ->where(function (Builder $query) use ($servicioIds, $gastoIds, $productoIds): void {
                if ($servicioIds->isNotEmpty()) {
                    $query->orWhere(fn (Builder $query) => $query
                        ->where('historialable_type', OfertaServicio::class)
                        ->whereIn('historialable_id', $servicioIds));
                }

                if ($gastoIds->isNotEmpty()) {
                    $query->orWhere(fn (Builder $query) => $query
                        ->where('historialable_type', OfertaGasto::class)
                        ->whereIn('historialable_id', $gastoIds));
                }

                if ($productoIds->isNotEmpty()) {
                    $query->orWhere(fn (Builder $query) => $query
                        ->where('historialable_type', OfertaServicioProducto::class)
                        ->whereIn('historialable_id', $productoIds));
                }
            })
            ->latest('id')
            ->limit(200)
            ->get();

        $historiales->load(['historialable' => function (MorphTo $morphTo): void {
            $morphTo->morphWith([
                OfertaServicio::class => ['servicio'],
                OfertaGasto::class => ['gasto'],
                OfertaServicioProducto::class => ['producto'],
            ]);
        }]);

        return $historiales;
    }
}
