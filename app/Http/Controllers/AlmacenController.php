<?php

namespace App\Http\Controllers;

use App\Http\Requests\Almacen\StoreAlmacenRapidoRequest;
use App\Http\Requests\Almacen\StoreAlmacenRequest;
use App\Http\Requests\Almacen\UpdateAlmacenRequest;
use App\Models\Almacen;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class AlmacenController extends Controller
{
    /**
     * Listado de almacenes.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->limit(100)->toString();

        $almacenes = Almacen::query()
            ->withCount('inventarios')
            ->withSum('inventarios as stock_total', 'cantidad')
            ->when($search !== '', fn ($query) => $query->where('nombre', 'like', "%{$search}%"))
            ->orderBy('nombre')
            ->paginate(12)
            ->withQueryString();

        return view('almacenes.index', compact('almacenes', 'search'));
    }

    /**
     * Formulario de creación de almacén.
     */
    public function create(): View
    {
        return view('almacenes.form', [
            'almacen' => new Almacen(['activo' => true]),
        ]);
    }

    /**
     * Guardar un almacén.
     */
    public function store(StoreAlmacenRequest $request): RedirectResponse
    {
        try {
            Almacen::create($request->validated());

            return redirect()
                ->route('almacenes.index')
                ->with('success', 'Almacén creado correctamente.');
        } catch (Throwable $e) {
            Log::error('Error al crear almacén.', [
                'exception' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->with('error', 'No se pudo crear el almacén. Inténtelo nuevamente.');
        }
    }

    /**
     * Crear un almacén solo con su nombre desde el formulario de entradas.
     */
    public function storeRapido(StoreAlmacenRapidoRequest $request): JsonResponse
    {
        try {
            $almacen = Almacen::create([
                'nombre' => $request->validated('nombre'),
                'activo' => true,
            ]);

            return response()->json([
                'id' => $almacen->id,
                'nombre' => $almacen->nombre,
            ], 201);
        } catch (Throwable $e) {
            Log::error('Error al crear almacén rápido.', [
                'exception' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'message' => 'No se pudo crear el almacén. Inténtelo nuevamente.',
            ], 500);
        }
    }

    /**
     * Formulario de edición de almacén.
     */
    public function edit(Almacen $almacen): View
    {
        return view('almacenes.form', compact('almacen'));
    }

    /**
     * Actualizar un almacén.
     */
    public function update(UpdateAlmacenRequest $request, Almacen $almacen): RedirectResponse
    {
        try {
            $almacen->update($request->validated());

            return redirect()
                ->route('almacenes.index')
                ->with('success', 'Almacén actualizado correctamente.');
        } catch (Throwable $e) {
            Log::error('Error al actualizar almacén.', [
                'almacen_id' => $almacen->id,
                'exception' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->with('error', 'No se pudo actualizar el almacén. Inténtelo nuevamente.');
        }
    }

    /**
     * Eliminar un almacén sin existencias ni operaciones.
     */
    public function destroy(Almacen $almacen): RedirectResponse
    {
        $tieneExistencias = $almacen->inventarios()->where('cantidad', '>', 0)->exists();

        if ($tieneExistencias || $almacen->operaciones()->exists()) {
            return back()->with(
                'error',
                'No se puede eliminar el almacén porque tiene existencias u operaciones registradas.',
            );
        }

        try {
            DB::transaction(fn () => $almacen->delete());

            return redirect()
                ->route('almacenes.index')
                ->with('success', 'Almacén eliminado correctamente.');
        } catch (Throwable $e) {
            Log::error('Error al eliminar almacén.', [
                'almacen_id' => $almacen->id,
                'exception' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'No se pudo eliminar el almacén. Inténtelo nuevamente.');
        }
    }
}
