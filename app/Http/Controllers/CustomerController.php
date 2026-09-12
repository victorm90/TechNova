<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $modal = $request->string('modal')->toString();
        $modalCustomer = $request->integer('customer');

        $customers = Customer::query()
            ->when($search, function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('identity_card', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(7)
            ->withQueryString();

        return view('customers.index', compact('customers', 'search', 'modal', 'modalCustomer'));
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('customers.index', ['modal' => 'create']);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->validationRules());

        Customer::create($validated);

        return redirect()->route('customers.index')->with('success', 'Cliente creado correctamente.');
    }

    public function show(Customer $customer): View
    {
        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer): RedirectResponse
    {
        return redirect()->route('customers.index', ['modal' => 'edit', 'customer' => $customer->id]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate($this->validationRules($customer));

        $customer->update($validated);

        return redirect()->route('customers.index')->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Cliente eliminado correctamente.');
    }

    private function validationRules(?Customer $customer = null): array
    {
        $identityCardRule = 'unique:customers,identity_card';

        if ($customer !== null) {
            $identityCardRule .= ",{$customer->id}";
        }

        return [
            'name' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:80'],
            'phone' => ['required', 'string', 'max:15'],
            'identity_card' => ['required', 'string', 'max:15', $identityCardRule],
        ];
    }
}
