<?php

namespace App\Http\Requests\Inventario;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalidaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('registrar-movimientos') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'referencia' => trim((string) $this->input('referencia')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'almacen_id' => ['required', 'integer', 'exists:almacenes,id'],
            'referencia' => ['nullable', 'string', 'max:60'],
            'notas' => ['nullable', 'string', 'max:1000'],
            'lineas' => ['required', 'array', 'min:1'],
            'lineas.*.producto_id' => ['required', 'integer', 'distinct', 'exists:productos,id'],
            'lineas.*.cantidad' => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'almacen_id.required' => 'Selecciona el almacén del que saldrá el inventario.',
            'almacen_id.exists' => 'El almacén seleccionado no es válido.',
            'referencia.max' => 'La referencia no puede superar los 60 caracteres.',
            'notas.max' => 'Las notas no pueden superar los 1000 caracteres.',
            'lineas.required' => 'Debes agregar al menos un producto a la salida.',
            'lineas.min' => 'Debes agregar al menos un producto a la salida.',
            'lineas.*.producto_id.required' => 'Selecciona un producto en cada línea.',
            'lineas.*.producto_id.exists' => 'Uno de los productos seleccionados no es válido.',
            'lineas.*.producto_id.distinct' => 'No puedes repetir el mismo producto en una salida.',
            'lineas.*.cantidad.required' => 'Indica la cantidad en cada línea.',
            'lineas.*.cantidad.gt' => 'La cantidad debe ser mayor que cero.',
            'lineas.*.cantidad.numeric' => 'La cantidad debe ser un número.',
        ];
    }
}
