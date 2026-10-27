<?php

namespace App\Http\Controllers;

use App\Http\Requests\Proveedor\StoreProveedorRequest;
use App\Http\Requests\Proveedor\UpdateProveedorRequest;
use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class ProveedorController extends Controller
{
    /**
     * Listado de proveedores.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->limit(100)->toString();

        $proveedores = Proveedor::query()
            ->withCount('operaciones')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('nombre', 'like', "%{$search}%")
                        ->orWhere('contacto', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('identificacion_fiscal', 'like', "%{$search}%");
                });
            })
            ->orderBy('nombre')
            ->paginate(12)
            ->withQueryString();

        $resumen = [
            'total' => Proveedor::query()->count(),
            'activos' => Proveedor::query()->where('activo', true)->count(),
            'con_compras' => Proveedor::query()->whereHas('operaciones')->count(),
        ];

        return view('proveedores.index', compact('proveedores', 'search', 'resumen'));
    }

    /**
     * Formulario de creación de proveedor.
     */
    public function create(): View
    {
        return view('proveedores.form', [
            'proveedor' => new Proveedor(['activo' => true]),
        ]);
    }

    /**
     * Guardar un proveedor.
     */
    public function store(StoreProveedorRequest $request): RedirectResponse
    {
        try {
            $proveedor = Proveedor::create($request->validated());

            return redirect()
                ->route('proveedores.index')
                ->with('success', "Proveedor \"{$proveedor->nombre}\" creado correctamente.");
        } catch (Throwable $e) {
            Log::error('Error al crear proveedor.', [
                'exception' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->with('error', 'No se pudo crear el proveedor. Inténtelo nuevamente.');
        }
    }

    /**
     * Formulario de edición de proveedor.
     */
    public function edit(Proveedor $proveedor): View
    {
        return view('proveedores.form', compact('proveedor'));
    }

    /**
     * Actualizar los datos del proveedor.
     */
    public function update(UpdateProveedorRequest $request, Proveedor $proveedor): RedirectResponse
    {
        try {
            $proveedor->update($request->validated());

            return redirect()
                ->route('proveedores.index')
                ->with('success', 'Proveedor actualizado correctamente.');
        } catch (Throwable $e) {
            Log::error('Error al actualizar proveedor.', [
                'proveedor_id' => $proveedor->id,
                'exception' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->with('error', 'No se pudo actualizar el proveedor. Inténtelo nuevamente.');
        }
    }

    /**
     * Eliminar un proveedor sin operaciones asociadas.
     */
    public function destroy(Proveedor $proveedor): RedirectResponse
    {
        if ($proveedor->operaciones()->exists()) {
            return back()->with(
                'error',
                'No se puede eliminar el proveedor porque tiene entradas registradas. Desactívalo en su lugar.',
            );
        }

        try {
            DB::transaction(fn () => $proveedor->delete());

            return redirect()
                ->route('proveedores.index')
                ->with('success', 'Proveedor eliminado correctamente.');
        } catch (Throwable $e) {
            Log::error('Error al eliminar proveedor.', [
                'proveedor_id' => $proveedor->id,
                'exception' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'No se pudo eliminar el proveedor. Inténtelo nuevamente.');
        }
    }
}
