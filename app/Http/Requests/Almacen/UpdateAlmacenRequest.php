<?php

namespace App\Http\Requests\Almacen;

use App\Models\Almacen;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAlmacenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-productos') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nombre' => trim((string) $this->input('nombre')),
            'activo' => $this->boolean('activo'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $almacen = $this->route('almacen');

        return [
            'nombre' => [
                'required',
                'string',
                'min:2',
                'max:80',
                Rule::unique('almacenes', 'nombre')
                    ->ignore($almacen instanceof Almacen ? $almacen->id : null),
            ],
            'direccion' => ['nullable', 'string', 'max:180'],
            'telefono' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s().]{6,20}$/'],
            'activo' => ['boolean'],
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
            'direccion.max' => 'La dirección no puede superar los 180 caracteres.',
            'telefono.regex' => 'El teléfono no tiene un formato válido.',
        ];
    }
}
