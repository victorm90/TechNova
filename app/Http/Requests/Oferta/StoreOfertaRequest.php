<?php

namespace App\Http\Requests\Oferta;

use App\Models\Oferta;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreOfertaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-ofertas') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'estado' => trim((string) $this->input('estado')),
            'servicios' => $this->normalizarLineas($this->input('servicios'), 'servicio_id'),
            'gastos' => $this->normalizarLineas($this->input('gastos'), 'gasto_id'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:clientes,id'],
            'fecha' => ['required', 'date'],
            'estado' => ['required', 'string', Rule::in(array_keys(Oferta::estados()))],

            'servicios' => ['nullable', 'array'],
            'servicios.*.servicio_id' => ['required', 'integer', 'distinct', 'exists:servicios,id'],
            'servicios.*.cantidad' => ['required', 'numeric', 'decimal:0,2', 'gt:0'],
            'servicios.*.precio_unitario' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],

            'gastos' => ['nullable', 'array'],
            'gastos.*.gasto_id' => ['required', 'integer', 'distinct', 'exists:gastos,id'],
            'gastos.*.cantidad' => ['required', 'numeric', 'decimal:0,2', 'gt:0'],
            'gastos.*.precio_unitario' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (empty($this->input('servicios')) && empty($this->input('gastos'))) {
                $validator->errors()->add(
                    'servicios',
                    'Debe agregar al menos un servicio o un gasto a la oferta.',
                );
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_id.required' => 'Seleccione un cliente.',
            'customer_id.exists' => 'El cliente seleccionado no existe.',
            'fecha.required' => 'La fecha de la oferta es obligatoria.',
            'fecha.date' => 'La fecha de la oferta no es válida.',
            'estado.required' => 'Seleccione el estado de la oferta.',
            'estado.in' => 'El estado seleccionado no es válido.',

            'servicios.*.servicio_id.required' => 'Seleccione un servicio en cada línea.',
            'servicios.*.servicio_id.exists' => 'El servicio seleccionado no existe.',
            'servicios.*.servicio_id.distinct' => 'No repita el mismo servicio en la oferta.',
            'servicios.*.cantidad.required' => 'Indique la cantidad del servicio.',
            'servicios.*.cantidad.numeric' => 'La cantidad del servicio debe ser un número.',
            'servicios.*.cantidad.decimal' => 'La cantidad del servicio admite como máximo dos decimales.',
            'servicios.*.cantidad.gt' => 'La cantidad del servicio debe ser mayor que cero.',
            'servicios.*.precio_unitario.required' => 'Indique el precio unitario del servicio.',
            'servicios.*.precio_unitario.numeric' => 'El precio del servicio debe ser un número.',
            'servicios.*.precio_unitario.decimal' => 'El precio del servicio admite como máximo dos decimales.',
            'servicios.*.precio_unitario.min' => 'El precio del servicio no puede ser negativo.',

            'gastos.*.gasto_id.required' => 'Seleccione un gasto en cada línea.',
            'gastos.*.gasto_id.exists' => 'El gasto seleccionado no existe.',
            'gastos.*.gasto_id.distinct' => 'No repita el mismo gasto en la oferta.',
            'gastos.*.cantidad.required' => 'Indique la cantidad del gasto.',
            'gastos.*.cantidad.numeric' => 'La cantidad del gasto debe ser un número.',
            'gastos.*.cantidad.decimal' => 'La cantidad del gasto admite como máximo dos decimales.',
            'gastos.*.cantidad.gt' => 'La cantidad del gasto debe ser mayor que cero.',
            'gastos.*.precio_unitario.required' => 'Indique el precio unitario del gasto.',
            'gastos.*.precio_unitario.numeric' => 'El precio del gasto debe ser un número.',
            'gastos.*.precio_unitario.decimal' => 'El precio del gasto admite como máximo dos decimales.',
            'gastos.*.precio_unitario.min' => 'El precio del gasto no puede ser negativo.',
        ];
    }

    /**
     * Normalizar las líneas enviadas por el formulario y descartar filas vacías.
     *
     * @return array<int, array<string, mixed>>
     */
    private function normalizarLineas(mixed $lineas, string $clave): array
    {
        if (! is_array($lineas)) {
            return [];
        }

        $normalizadas = [];

        foreach ($lineas as $linea) {
            if (! is_array($linea)) {
                continue;
            }

            $id = $linea[$clave] ?? null;
            $cantidad = $this->normalizarNumero($linea['cantidad'] ?? null);
            $precio = $this->normalizarNumero($linea['precio_unitario'] ?? null);

            if ($this->lineaVacia($id, $cantidad, $precio)) {
                continue;
            }

            $normalizadas[] = [
                $clave => $id,
                'cantidad' => $cantidad,
                'precio_unitario' => $precio,
            ];
        }

        return $normalizadas;
    }

    private function lineaVacia(mixed $id, mixed $cantidad, mixed $precio): bool
    {
        return ($id === null || $id === '')
            && ($cantidad === null || $cantidad === '')
            && ($precio === null || $precio === '');
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
