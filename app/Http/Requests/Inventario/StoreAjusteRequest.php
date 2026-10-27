<?php

namespace App\Http\Requests\Inventario;

use Illuminate\Foundation\Http\FormRequest;

class StoreAjusteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('registrar-movimientos') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'inventario_id' => ['required', 'integer', 'exists:inventarios,id'],
            'cantidad' => ['required', 'integer', 'min:0', 'max:99999999'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'inventario_id.required' => 'Selecciona un producto existente en el almacén.',
            'inventario_id.exists' => 'El producto seleccionado no existe en el almacén.',
            'cantidad.required' => 'Indica la cantidad real en stock.',
            'cantidad.integer' => 'La cantidad debe ser un número entero.',
            'cantidad.min' => 'La cantidad en stock no puede ser negativa.',
            'cantidad.max' => 'La cantidad es demasiado alta.',
        ];
    }
}
