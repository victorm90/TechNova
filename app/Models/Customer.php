<?php

namespace App\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

#[Fillable(['name', 'address', 'phone', 'identity_card'])]
class Customer extends Model
{
    protected $table = 'clientes';

    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    /**
     * @return array<string, array<int, string|ValidationRule>>
     */
    public static function validationRules(?self $customer = null): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:10',
                'max:50',
                Rule::unique('clientes', 'name')->ignore($customer?->id),
            ],
            'address' => ['required', 'string', 'max:100'],
            'phone' => [
                'required',
                'string',
                'regex:/^\d{8,12}$/',
                Rule::unique('clientes', 'phone')->ignore($customer?->id),
            ],
            'identity_card' => [
                'required',
                'string',
                'regex:/^\d{11}$/',
                Rule::unique('clientes', 'identity_card')->ignore($customer?->id),
            ],
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function validationMessages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre debe ser texto.',
            'name.min' => 'El nombre debe tener al menos 10 caracteres.',
            'name.max' => 'El nombre no puede superar los 50 caracteres.',
            'name.unique' => 'Ese nombre ya está registrado.',
            'address.required' => 'La dirección es obligatoria.',
            'address.string' => 'La dirección debe ser texto.',
            'address.max' => 'La dirección no puede superar los 100 caracteres.',
            'phone.required' => 'El teléfono es obligatorio.',
            'phone.string' => 'El teléfono debe ser texto numérico.',
            'phone.regex' => 'El teléfono debe contener solo números y tener entre 8 y 12 dígitos.',
            'phone.unique' => 'Ese teléfono ya está registrado.',
            'identity_card.required' => 'El carnet de identidad es obligatorio.',
            'identity_card.string' => 'El carnet de identidad debe ser texto numérico.',
            'identity_card.regex' => 'El carnet de identidad debe contener exactamente 11 dígitos.',
            'identity_card.unique' => 'Ese carnet de identidad ya está registrado.',
        ];
    }
}
