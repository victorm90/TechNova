<?php

namespace App\Http\Requests\Servicio;

use Illuminate\Foundation\Http\FormRequest;

class StoreServicioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-nomencladores') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nombre' => trim((string) $this->input('nombre')),
            'precio_unitario' => $this->normalizarNumero($this->input('precio_unitario')),
            'activo' => $this->boolean('activo'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'min:2', 'max:255'],
            'unidad_medida_id' => ['required', 'integer', 'exists:unidad_medidas,id'],
            'precio_unitario' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'activo' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del servicio es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max' => 'El nombre no puede superar los 255 caracteres.',
            'unidad_medida_id.required' => 'Seleccione una unidad de medida.',
            'unidad_medida_id.exists' => 'La unidad de medida seleccionada no existe.',
            'precio_unitario.required' => 'El precio unitario es obligatorio.',
            'precio_unitario.numeric' => 'El precio unitario debe ser un número.',
            'precio_unitario.decimal' => 'El precio unitario admite como máximo dos decimales.',
            'precio_unitario.min' => 'El precio unitario no puede ser negativo.',
            'precio_unitario.max' => 'El precio unitario es demasiado alto.',
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
