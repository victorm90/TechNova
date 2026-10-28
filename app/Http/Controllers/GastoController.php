<?php

namespace App\Http\Controllers;

use App\Http\Requests\Gasto\StoreGastoRequest;
use App\Http\Requests\Gasto\UpdateGastoRequest;
use App\Models\Gasto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class GastoController extends Controller
{
    /**
     * Listado de gastos.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->limit(100)->toString();

        $gastos = Gasto::query()
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where('nombre', 'like', "%{$search}%");
            })
            ->orderBy('nombre')
            ->paginate(12)
            ->withQueryString();

        return view('gastos.index', compact('gastos', 'search'));
    }

    /**
     * Formulario de creación de gasto.
     */
    public function create(): View
    {
        return view('gastos.form', [
            'gasto' => new Gasto(['activo' => true]),
        ]);
    }

    /**
     * Guardar un gasto.
     */
    public function store(StoreGastoRequest $request): RedirectResponse
    {
        try {
            Gasto::create($request->validated());

            return redirect()
                ->route('gastos.index')
                ->with('success', 'Gasto creado correctamente.');
        } catch (Throwable $e) {
            Log::error('Error al crear gasto.', [
                'exception' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->with('error', 'No se pudo crear el gasto. Inténtelo nuevamente.');
        }
    }

    /**
     * Formulario de edición de gasto.
     */
    public function edit(Gasto $gasto): View
    {
        return view('gastos.form', compact('gasto'));
    }

    /**
     * Actualizar un gasto.
     */
    public function update(UpdateGastoRequest $request, Gasto $gasto): RedirectResponse
    {
        try {
            $gasto->update($request->validated());

            return redirect()
                ->route('gastos.index')
                ->with('success', 'Gasto actualizado correctamente.');
        } catch (Throwable $e) {
            Log::error('Error al actualizar gasto.', [
                'gasto_id' => $gasto->id,
                'exception' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->with('error', 'No se pudo actualizar el gasto. Inténtelo nuevamente.');
        }
    }

    /**
     * Eliminar un gasto que no esté asociado a ofertas.
     */
    public function destroy(Gasto $gasto): RedirectResponse
    {
        if ($gasto->ofertaGastos()->exists()) {
            return back()->with(
                'error',
                'No se puede eliminar el gasto porque está asociado a una oferta.',
            );
        }

        try {
            DB::transaction(fn () => $gasto->delete());

            return redirect()
                ->route('gastos.index')
                ->with('success', 'Gasto eliminado correctamente.');
        } catch (Throwable $e) {
            Log::error('Error al eliminar gasto.', [
                'gasto_id' => $gasto->id,
                'exception' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'No se pudo eliminar el gasto. Inténtelo nuevamente.');
        }
    }
}
