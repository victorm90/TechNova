<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class CustomerController extends Controller
{
    /**
     * Lista de clientes.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')
            ->trim()
            ->limit(100)
            ->toString();

        $customers = Customer::query()
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('identity_card', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        $modal = $request->string('modal')->toString();
        $modalCustomer = $modal === 'edit' && $request->integer('customer')
            ? Customer::find($request->integer('customer'))
            : null;

        return view('customers.index', compact(
            'customers',
            'search',
            'modal',
            'modalCustomer'
        ));
    }

    /**
     * Mostrar formulario de creación.
     */
    public function create(): RedirectResponse
    {
        return redirect()->route('customers.index', [
            'modal' => 'create',
        ]);
    }

    /**
     * Guardar un nuevo cliente.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            Customer::validationRules(),
            Customer::validationMessages()
        );

        try {
            DB::transaction(function () use ($validated) {
                Customer::create($validated);
            });

            return redirect()
                ->route('customers.index')
                ->with('success', 'Cliente creado correctamente.');

        } catch (Throwable $e) {

            Log::error('Error al crear cliente.', [
                'exception' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'No se pudo crear el cliente. Inténtelo nuevamente.');
        }
    }

    /**
     * Mostrar un cliente.
     */
    public function show(Customer $customer): View
    {
        return view('customers.show', compact('customer'));
    }

    /**
     * Mostrar formulario de edición.
     */
    public function edit(Customer $customer): RedirectResponse
    {
        return redirect()->route('customers.index', [
            'modal' => 'edit',
            'customer' => $customer->id,
        ]);
    }

    /**
     * Actualizar cliente.
     */
    public function update(
        Request $request,
        Customer $customer
    ): RedirectResponse {
        $validated = $request->validate(
            Customer::validationRules($customer),
            Customer::validationMessages()
        );

        try {
            DB::transaction(function () use ($customer, $validated) {
                $customer->update($validated);
            });

            return redirect()
                ->route('customers.index')
                ->with('success', 'Cliente actualizado correctamente.');

        } catch (Throwable $e) {

            Log::error('Error al actualizar cliente.', [
                'customer_id' => $customer->id,
                'user_id' => auth()->id(),
                'exception' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'No se pudo actualizar el cliente. Inténtelo nuevamente.');
        }
    }

    /**
     * Eliminar cliente.
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        try {
            DB::transaction(function () use ($customer) {
                $customer->delete();
            });

            return redirect()
                ->route('customers.index')
                ->with('success', 'Cliente eliminado correctamente.');

        } catch (Throwable $e) {

            Log::error('Error al eliminar cliente.', [
                'customer_id' => $customer->id,
                'user_id' => auth()->id(),
                'exception' => $e->getMessage(),
            ]);

            return back()
                ->with('error', 'No se pudo eliminar el cliente. Inténtelo nuevamente.');
        }
    }
}
