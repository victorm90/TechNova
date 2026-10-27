<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoriaProducto\StoreCategoriaProductoRequest;
use App\Http\Requests\CategoriaProducto\UpdateCategoriaProductoRequest;
use App\Models\CategoriaProducto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class CategoriaProductoController extends Controller
{
    /**
     * Listado de categorías de productos.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->limit(100)->toString();

        $categorias = CategoriaProducto::query()
            ->withCount('productos')
            ->when($search !== '', fn ($query) => $query->where('nombre', 'like', "%{$search}%"))
            ->orderBy('nombre')
            ->paginate(12)
            ->withQueryString();

        return view('categorias.index', compact('categorias', 'search'));
    }

    /**
     * Formulario de creación de categoría.
     */
    public function create(): View
    {
        return view('categorias.form', [
            'categoria' => new CategoriaProducto(['activo' => true]),
        ]);
    }

    /**
     * Guardar una categoría.
     */
    public function store(StoreCategoriaProductoRequest $request): RedirectResponse
    {
        try {
            CategoriaProducto::create($request->validated());

            return redirect()
                ->route('categorias.index')
                ->with('success', 'Categoría creada correctamente.');
        } catch (Throwable $e) {
            Log::error('Error al crear categoría de producto.', [
                'exception' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->with('error', 'No se pudo crear la categoría. Inténtelo nuevamente.');
        }
    }

    /**
     * Formulario de edición de categoría.
     */
    public function edit(CategoriaProducto $categoria): View
    {
        return view('categorias.form', compact('categoria'));
    }

    /**
     * Actualizar una categoría.
     */
    public function update(UpdateCategoriaProductoRequest $request, CategoriaProducto $categoria): RedirectResponse
    {
        try {
            $categoria->update($request->validated());

            return redirect()
                ->route('categorias.index')
                ->with('success', 'Categoría actualizada correctamente.');
        } catch (Throwable $e) {
            Log::error('Error al actualizar categoría de producto.', [
                'categoria_id' => $categoria->id,
                'exception' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->with('error', 'No se pudo actualizar la categoría. Inténtelo nuevamente.');
        }
    }

    /**
     * Eliminar una categoría sin productos.
     */
    public function destroy(CategoriaProducto $categoria): RedirectResponse
    {
        if ($categoria->productos()->exists()) {
            return back()->with(
                'error',
                'No se puede eliminar la categoría porque tiene productos asociados.',
            );
        }

        try {
            DB::transaction(fn () => $categoria->delete());

            return redirect()
                ->route('categorias.index')
                ->with('success', 'Categoría eliminada correctamente.');
        } catch (Throwable $e) {
            Log::error('Error al eliminar categoría de producto.', [
                'categoria_id' => $categoria->id,
                'exception' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'No se pudo eliminar la categoría. Inténtelo nuevamente.');
        }
    }
}
