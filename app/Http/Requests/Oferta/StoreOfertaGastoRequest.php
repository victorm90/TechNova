<?php

namespace App\Http\Requests\Oferta;

use Illuminate\Foundation\Http\FormRequest;

class StoreOfertaGastoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-ofertas') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'cantidad' => $this->normalizarNumero($this->input('cantidad')),
            'precio_unitario' => $this->normalizarNumero($this->input('precio_unitario')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'gasto_id' => ['required', 'integer', 'exists:gastos,id'],
            'cantidad' => ['required', 'numeric', 'decimal:0,2', 'gt:0'],
            'precio_unitario' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'gasto_id.required' => 'Seleccione un gasto.',
            'gasto_id.exists' => 'El gasto seleccionado no existe.',
            'cantidad.required' => 'Indique la cantidad del gasto.',
            'cantidad.numeric' => 'La cantidad debe ser un número.',
            'cantidad.gt' => 'La cantidad debe ser mayor que cero.',
            'precio_unitario.numeric' => 'El precio debe ser un número.',
            'precio_unitario.min' => 'El precio no puede ser negativo.',
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
