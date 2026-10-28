<?php

namespace App\Http\Requests\Oferta;

use Illuminate\Foundation\Http\FormRequest;

class FacturarOfertaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-ofertas') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'fecha' => $this->input('fecha') ?: null,
            'nota' => $this->limpiarTexto($this->input('nota')),
            'forma_pago' => $this->limpiarTexto($this->input('forma_pago')),
            'observaciones' => $this->limpiarTexto($this->input('observaciones')),
            'firma_pie' => $this->limpiarTexto($this->input('firma_pie')),
            'servicios' => $this->normalizarAjustes($this->input('servicios'), 'precio_unitario'),
            'gastos' => $this->normalizarAjustes($this->input('gastos'), 'precio_unitario'),
            'productos' => $this->normalizarAjustes($this->input('productos'), 'precio_venta'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'almacen_id' => ['required', 'integer', 'exists:almacenes,id'],
            'fecha' => ['nullable', 'date'],
            'nota' => ['nullable', 'string', 'max:2000'],
            'forma_pago' => ['nullable', 'string', 'max:120'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
            'firma_pie' => ['nullable', 'string', 'max:150'],

            'servicios' => ['present', 'array'],
            'servicios.*.id' => ['required', 'integer', 'distinct'],
            'servicios.*.cantidad' => ['required', 'numeric', 'decimal:0,2', 'gt:0'],
            'servicios.*.precio_unitario' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],

            'gastos' => ['present', 'array'],
            'gastos.*.id' => ['required', 'integer', 'distinct'],
            'gastos.*.cantidad' => ['required', 'numeric', 'decimal:0,2', 'gt:0'],
            'gastos.*.precio_unitario' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],

            'productos' => ['present', 'array'],
            'productos.*.id' => ['required', 'integer', 'distinct'],
            'productos.*.cantidad' => ['required', 'integer', 'min:1', 'max:1000000'],
            'productos.*.precio_venta' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'almacen_id.required' => 'Seleccione el almacén del que se descontarán los productos.',
            'almacen_id.exists' => 'El almacén seleccionado no existe.',
            'servicios.*.id.required' => 'Falta identificar una línea de servicio.',
            'servicios.*.cantidad.gt' => 'La cantidad real del servicio debe ser mayor que cero.',
            'servicios.*.precio_unitario.min' => 'El precio final del servicio no puede ser negativo.',
            'gastos.*.id.required' => 'Falta identificar una línea de gasto.',
            'gastos.*.cantidad.gt' => 'La cantidad real del gasto debe ser mayor que cero.',
            'gastos.*.precio_unitario.min' => 'El precio final del gasto no puede ser negativo.',
            'productos.*.id.required' => 'Falta identificar una línea de producto.',
            'productos.*.cantidad.integer' => 'La cantidad real del producto debe ser un número entero.',
            'productos.*.cantidad.min' => 'La cantidad real del producto debe ser al menos 1.',
            'productos.*.precio_venta.min' => 'El precio final del producto no puede ser negativo.',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizarAjustes(mixed $lineas, string $campoPrecio): array
    {
        if (! is_array($lineas)) {
            return [];
        }

        $normalizadas = [];

        foreach ($lineas as $linea) {
            if (! is_array($linea) || ! isset($linea['id'])) {
                continue;
            }

            $normalizadas[] = [
                'id' => (int) $linea['id'],
                'cantidad' => $this->normalizarNumero($linea['cantidad'] ?? null),
                $campoPrecio => $this->normalizarNumero($linea[$campoPrecio] ?? null),
            ];
        }

        return $normalizadas;
    }

    private function normalizarNumero(mixed $valor): mixed
    {
        if (! is_string($valor)) {
            return $valor;
        }

        $valor = trim($valor);

        return $valor === '' ? null : str_replace(',', '.', $valor);
    }

    private function limpiarTexto(mixed $valor): mixed
    {
        if (! is_string($valor)) {
            return $valor;
        }

        $valor = trim($valor);

        return $valor === '' ? null : $valor;
    }
}
