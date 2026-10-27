<?php

namespace App\Http\Requests\Almacen;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAlmacenRapidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('registrar-movimientos') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nombre' => trim((string) $this->input('nombre')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'min:2', 'max:80', Rule::unique('almacenes', 'nombre')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del almacén es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max' => 'El nombre no puede superar los 80 caracteres.',
            'nombre.unique' => 'Ya existe un almacén con ese nombre.',
        ];
    }
}
