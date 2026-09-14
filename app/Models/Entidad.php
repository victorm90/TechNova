<?php

namespace App\Models;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\Rule;

#[Fillable(['nombre', 'direccion', 'provincia_id', 'numero_cuenta', 'telefono'])]
class Entidad extends Model
{
    protected $table = 'entidades';

    /**
     * Get the provincia that owns the entidad.
     */
    public function provincia(): BelongsTo
    {
        return $this->belongsTo(Provincia::class);
    }

    /**
     * Get the validation rules for an entidad.
     *
     * @return array<string, array<int, string|ValidationRule>>
     */
    public static function validationRules(?self $entidad = null): array
    {
        return [
            'nombre' => [
                'required',
                'string',
                'min:5',
                'max:40',
                Rule::unique('entidades', 'nombre')->ignore($entidad?->id),
            ],
            'direccion' => ['required', 'string', 'max:100'],
            'provincia_id' => ['required', 'integer', 'exists:provincias,id'],
            'numero_cuenta' => [
                'nullable',
                'string',
                'regex:/^\d{15}$/',
                Rule::unique('entidades', 'numero_cuenta')->ignore($entidad?->id),
            ],
            'telefono' => ['required', 'string', 'regex:/^\d{8,12}$/'],
        ];
    }

    /**
     * Get the validation messages for an entidad.
     *
     * @return array<string, string>
     */
    public static function validationMessages(): array
    {
        return [
            'nombre.required' => 'El nombre de la entidad es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 5 caracteres.',
            'nombre.max' => 'El nombre no puede superar los 40 caracteres.',
            'nombre.unique' => 'Ese nombre de entidad ya está registrado.',
            'direccion.required' => 'La dirección es obligatoria.',
            'direccion.max' => 'La dirección no puede superar los 100 caracteres.',
            'provincia_id.required' => 'La provincia es obligatoria.',
            'provincia_id.exists' => 'La provincia seleccionada no es válida.',
            'numero_cuenta.regex' => 'El número de cuenta debe contener exactamente 15 dígitos.',
            'numero_cuenta.unique' => 'Ese número de cuenta ya está registrado.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.regex' => 'El teléfono debe contener entre 8 y 12 dígitos.',
        ];
    }
}
