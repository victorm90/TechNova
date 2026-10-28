<?php

namespace App\Http\Requests\Oferta;

use Illuminate\Foundation\Http\FormRequest;

class StoreOfertaServicioRequest extends FormRequest
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
            'servicio_id' => ['required', 'integer', 'exists:servicios,id'],
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
            'servicio_id.required' => 'Seleccione un servicio.',
            'servicio_id.exists' => 'El servicio seleccionado no existe.',
            'cantidad.required' => 'Indique la cantidad del servicio.',
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
