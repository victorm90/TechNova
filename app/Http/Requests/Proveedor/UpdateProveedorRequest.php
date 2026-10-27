<?php

namespace App\Http\Requests\Proveedor;

use App\Models\Proveedor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-proveedores') ?? false;
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
        $proveedor = $this->route('proveedor');

        return [
            'nombre' => [
                'required',
                'string',
                'min:2',
                'max:120',
                Rule::unique('proveedores', 'nombre')
                    ->ignore($proveedor instanceof Proveedor ? $proveedor->id : null),
            ],
            'contacto' => ['nullable', 'string', 'max:120'],
            'telefono' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s().]{6,20}$/'],
            'email' => ['nullable', 'string', 'email', 'max:120'],
            'direccion' => ['nullable', 'string', 'max:180'],
            'identificacion_fiscal' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('proveedores', 'identificacion_fiscal')
                    ->ignore($proveedor instanceof Proveedor ? $proveedor->id : null),
            ],
            'notas' => ['nullable', 'string', 'max:1000'],
            'activo' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del proveedor es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max' => 'El nombre no puede superar los 120 caracteres.',
            'nombre.unique' => 'Ya existe un proveedor con ese nombre.',
            'contacto.max' => 'El contacto no puede superar los 120 caracteres.',
            'telefono.regex' => 'El teléfono no tiene un formato válido.',
            'telefono.max' => 'El teléfono no puede superar los 20 caracteres.',
            'email.email' => 'El correo electrónico no es válido.',
            'email.max' => 'El correo no puede superar los 120 caracteres.',
            'direccion.max' => 'La dirección no puede superar los 180 caracteres.',
            'identificacion_fiscal.max' => 'La identificación fiscal no puede superar los 30 caracteres.',
            'identificacion_fiscal.unique' => 'Esa identificación fiscal ya está registrada.',
            'notas.max' => 'Las notas no pueden superar los 1000 caracteres.',
        ];
    }
}
