<?php

namespace App\Http\Requests\Oferta;

use Illuminate\Foundation\Http\FormRequest;

class StoreOfertaRapidaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-ofertas') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $nombre = $this->input('cliente_nombre');

        $this->merge([
            'cliente_nombre' => is_string($nombre) ? trim($nombre) : $nombre,
            'customer_id' => $this->input('customer_id') ?: null,
            'fecha' => $this->input('fecha') ?: null,
            'almacen_id' => $this->input('almacen_id') ?: null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'cliente_nombre' => ['required_without:customer_id', 'nullable', 'string', 'min:2', 'max:50'],
            'customer_id' => ['nullable', 'integer', 'exists:clientes,id'],
            'fecha' => ['nullable', 'date'],
            'almacen_id' => ['nullable', 'integer', 'exists:almacenes,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cliente_nombre.required_without' => 'Indique el nombre del cliente.',
            'cliente_nombre.min' => 'El nombre del cliente debe tener al menos 2 caracteres.',
            'cliente_nombre.max' => 'El nombre del cliente no puede superar los 50 caracteres.',
            'customer_id.exists' => 'El cliente seleccionado no existe.',
            'fecha.date' => 'La fecha de la oferta no es válida.',
            'almacen_id.exists' => 'El almacén seleccionado no existe.',
        ];
    }
}
