<?php

namespace App\Http\Requests\Inventario;

use App\Models\Almacen;
use Illuminate\Foundation\Http\FormRequest;

class StoreEntradaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('registrar-movimientos') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('almacen_id')) {
            $this->merge([
                'almacen_id' => Almacen::query()->activos()->orderBy('nombre')->value('id'),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'almacen_id' => ['required', 'integer', 'exists:almacenes,id'],
            'proveedor_id' => ['nullable', 'integer', 'exists:proveedores,id'],
            'lineas' => ['required', 'array', 'min:1'],
            'lineas.*.producto_id' => ['required', 'integer', 'distinct', 'exists:productos,id'],
            'lineas.*.precio_compra' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'lineas.*.stock_minimo' => ['required', 'integer', 'min:0', 'max:99999999'],
            'lineas.*.cantidad' => ['required', 'integer', 'gt:0', 'max:99999999'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'almacen_id.required' => 'No hay un almacén activo disponible para registrar la entrada.',
            'almacen_id.exists' => 'El almacén seleccionado no es válido.',
            'proveedor_id.exists' => 'El proveedor seleccionado no es válido.',
            'lineas.required' => 'Debes agregar al menos un producto a la entrada.',
            'lineas.min' => 'Debes agregar al menos un producto a la entrada.',
            'lineas.*.producto_id.required' => 'Selecciona un producto en cada línea.',
            'lineas.*.producto_id.exists' => 'Uno de los productos seleccionados no es válido.',
            'lineas.*.producto_id.distinct' => 'No puedes repetir el mismo producto en una entrada.',
            'lineas.*.precio_compra.required' => 'Indica el precio de compra en cada línea.',
            'lineas.*.precio_compra.numeric' => 'El precio de compra debe ser un número.',
            'lineas.*.precio_compra.min' => 'El precio de compra no puede ser negativo.',
            'lineas.*.precio_compra.max' => 'El precio de compra es demasiado alto.',
            'lineas.*.stock_minimo.required' => 'Indica el stock mínimo en cada línea.',
            'lineas.*.stock_minimo.integer' => 'El stock mínimo debe ser un número entero.',
            'lineas.*.stock_minimo.min' => 'El stock mínimo no puede ser negativo.',
            'lineas.*.stock_minimo.max' => 'El stock mínimo es demasiado alto.',
            'lineas.*.cantidad.required' => 'Indica la cantidad a ingresar en cada línea.',
            'lineas.*.cantidad.integer' => 'La cantidad debe ser un número entero.',
            'lineas.*.cantidad.gt' => 'La cantidad debe ser mayor que cero.',
            'lineas.*.cantidad.max' => 'La cantidad es demasiado alta.',
        ];
    }
}
