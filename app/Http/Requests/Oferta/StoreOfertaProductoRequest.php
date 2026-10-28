<?php

namespace App\Http\Requests\Oferta;

use Illuminate\Foundation\Http\FormRequest;

class StoreOfertaProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-ofertas') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'cantidad' => $this->normalizarNumero($this->input('cantidad')),
            'precio_venta' => $this->normalizarNumero($this->input('precio_venta')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'producto_id' => ['required', 'integer', 'exists:productos,id'],
            'cantidad' => ['required', 'integer', 'min:1', 'max:1000000'],
            'precio_venta' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'producto_id.required' => 'Seleccione un producto del almacén.',
            'producto_id.exists' => 'El producto seleccionado no existe.',
            'cantidad.required' => 'Indique la cantidad a utilizar.',
            'cantidad.integer' => 'La cantidad debe ser un número entero.',
            'cantidad.min' => 'La cantidad debe ser al menos 1.',
            'precio_venta.numeric' => 'El precio de venta debe ser un número.',
            'precio_venta.min' => 'El precio de venta no puede ser negativo.',
        ];
    }

    private function normalizarNumero(mixed $valor): mixed
    {
        if (! is_string($valor)) {
            return $valor;
        }

        $valor = trim($valor);

        return $valor === '' ? null : str_replace(',', '.', $valor);
    }
}
