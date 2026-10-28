<?php

namespace App\Http\Controllers;

use App\Http\Requests\Servicio\StoreServicioRequest;
use App\Http\Requests\Servicio\UpdateServicioRequest;
use App\Models\Servicio;
use App\Models\UnidadMedida;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class ServicioController extends Controller
{
    /**
     * Listado de servicios.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->limit(100)->toString();

        $servicios = Servicio::query()
            ->with('unidadMedida')
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('nombre', 'like', "%{$search}%")
                        ->orWhereHas('unidadMedida', function (Builder $unitQuery) use ($search) {
                            $unitQuery->where('nombre', 'like', "%{$search}%")
                                ->orWhere('abreviatura', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('nombre')
            ->paginate(12)
            ->withQueryString();

        return view('servicios.index', compact('servicios', 'search'));
    }

    /**
     * Formulario de creación de servicio.
     */
    public function create(): View
    {
        return view('servicios.form', [
            'servicio' => new Servicio(['activo' => true]),
            'unidades' => UnidadMedida::query()->orderBy('nombre')->get(),
        ]);
    }

    /**
     * Guardar un servicio.
     */
    public function store(StoreServicioRequest $request): RedirectResponse
    {
        try {
            Servicio::create($request->validated());

            return redirect()
                ->route('servicios.index')
                ->with('success', 'Servicio creado correctamente.');
        } catch (Throwable $e) {
            Log::error('Error al crear servicio.', [
                'exception' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->with('error', 'No se pudo crear el servicio. Inténtelo nuevamente.');
        }
    }

    /**
     * Formulario de edición de servicio.
     */
    public function edit(Servicio $servicio): View
    {
        return view('servicios.form', [
            'servicio' => $servicio,
            'unidades' => UnidadMedida::query()->orderBy('nombre')->get(),
        ]);
    }

    /**
     * Actualizar un servicio.
     */
    public function update(UpdateServicioRequest $request, Servicio $servicio): RedirectResponse
    {
        try {
            $servicio->update($request->validated());

            return redirect()
                ->route('servicios.index')
                ->with('success', 'Servicio actualizado correctamente.');
        } catch (Throwable $e) {
            Log::error('Error al actualizar servicio.', [
                'servicio_id' => $servicio->id,
                'exception' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->with('error', 'No se pudo actualizar el servicio. Inténtelo nuevamente.');
        }
    }

    /**
     * Eliminar un servicio que no esté asociado a ofertas.
     */
    public function destroy(Servicio $servicio): RedirectResponse
    {
        if ($servicio->ofertaServicios()->exists()) {
            return back()->with(
                'error',
                'No se puede eliminar el servicio porque está asociado a una oferta.',
            );
        }

        try {
            DB::transaction(fn () => $servicio->delete());

            return redirect()
                ->route('servicios.index')
                ->with('success', 'Servicio eliminado correctamente.');
        } catch (Throwable $e) {
            Log::error('Error al eliminar servicio.', [
                'servicio_id' => $servicio->id,
                'exception' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'No se pudo eliminar el servicio. Inténtelo nuevamente.');
        }
    }
}
