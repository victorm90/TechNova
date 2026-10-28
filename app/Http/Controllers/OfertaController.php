<?php

namespace App\Http\Controllers;

use App\Http\Requests\Oferta\StoreOfertaRequest;
use App\Http\Requests\Oferta\UpdateOfertaRequest;
use App\Models\Customer;
use App\Models\Gasto;
use App\Models\Oferta;
use App\Models\Servicio;
use App\Services\OfertaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class OfertaController extends Controller
{
    public function __construct(private readonly OfertaService $ofertas) {}

    /**
     * Listado de ofertas.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->limit(100)->toString();
        $estado = $request->string('estado')->trim()->toString();

        $ofertas = Oferta::query()
            ->with(['customer', 'servicios', 'gastos'])
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

        return view('ofertas.index', compact('ofertas', 'search', 'estado'));
    }

    /**
     * Formulario de creación de oferta.
     */
    public function create(): View
    {
        return view('ofertas.form', [
            'oferta' => new Oferta([
                'fecha' => now()->toDateString(),
                'estado' => Oferta::ESTADO_BORRADOR,
            ]),
            'clientes' => Customer::query()->orderBy('name')->get(),
            'servicios' => Servicio::query()
                ->activos()
                ->with('unidadMedida')
                ->orderBy('nombre')
                ->get(),
            'gastos' => Gasto::query()
                ->activos()
                ->orderBy('nombre')
                ->get(),
        ]);
    }

    /**
     * Guardar una oferta con sus líneas.
     */
    public function store(StoreOfertaRequest $request): RedirectResponse
    {
        try {
            $oferta = $this->ofertas->crear($request->validated());

            return redirect()
                ->route('ofertas.show', $oferta)
                ->with('success', 'Oferta creada correctamente.');
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
            'servicios.servicio.unidadMedida',
            'gastos.gasto',
        ]);

        return view('ofertas.show', compact('oferta'));
    }

    /**
     * Formulario de edición de oferta.
     */
    public function edit(Oferta $oferta): View
    {
        $oferta->load(['servicios', 'gastos']);

        $servicios = Servicio::query()
            ->where(function (Builder $query) use ($oferta) {
                $query->where('activo', true)
                    ->orWhereIn('id', $oferta->servicios->pluck('servicio_id'));
            })
            ->with('unidadMedida')
            ->orderBy('nombre')
            ->get();

        $gastos = Gasto::query()
            ->where(function (Builder $query) use ($oferta) {
                $query->where('activo', true)
                    ->orWhereIn('id', $oferta->gastos->pluck('gasto_id'));
            })
            ->orderBy('nombre')
            ->get();

        return view('ofertas.form', [
            'oferta' => $oferta,
            'clientes' => Customer::query()->orderBy('name')->get(),
            'servicios' => $servicios,
            'gastos' => $gastos,
        ]);
    }

    /**
     * Actualizar una oferta y sus líneas.
     */
    public function update(UpdateOfertaRequest $request, Oferta $oferta): RedirectResponse
    {
        try {
            $this->ofertas->actualizar($oferta, $request->validated());

            return redirect()
                ->route('ofertas.show', $oferta)
                ->with('success', 'Oferta actualizada correctamente.');
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
     * Eliminar una oferta y sus líneas.
     */
    public function destroy(Oferta $oferta): RedirectResponse
    {
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
}
