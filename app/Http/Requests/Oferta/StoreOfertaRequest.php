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
            'nota' => $this->limpiarTexto($this->input('nota')),
            'forma_pago' => $this->limpiarTexto($this->input('forma_pago')),
            'observaciones' => $this->limpiarTexto($this->input('observaciones')),
            'firma_pie' => $this->limpiarTexto($this->input('firma_pie')),
            'almacen_id' => $this->limpiarNumero($this->input('almacen_id')),
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
            'almacen_id' => ['nullable', 'integer', 'exists:almacenes,id'],
            'nota' => ['nullable', 'string', 'max:2000'],
            'forma_pago' => ['nullable', 'string', 'max:120'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
            'firma_pie' => ['nullable', 'string', 'max:150'],
            'vigencia_dias' => ['nullable', 'integer', 'min:1', 'max:365'],

            'servicios' => ['nullable', 'array'],
            'servicios.*.id' => ['nullable', 'integer', 'min:1'],
            'servicios.*.servicio_id' => ['required', 'integer', 'distinct', 'exists:servicios,id'],
            'servicios.*.cantidad' => ['required', 'numeric', 'decimal:0,2', 'gt:0'],
            'servicios.*.precio_unitario' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'servicios.*.productos' => ['nullable', 'array'],
            'servicios.*.productos.*.id' => ['nullable', 'integer', 'min:1'],
            'servicios.*.productos.*.producto_id' => ['required', 'integer', 'distinct', 'exists:productos,id'],
            'servicios.*.productos.*.cantidad' => ['required', 'integer', 'min:1', 'max:1000000'],
            'servicios.*.productos.*.precio_venta' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],

            'gastos' => ['nullable', 'array'],
            'gastos.*.id' => ['nullable', 'integer', 'min:1'],
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
            'almacen_id.exists' => 'El almacén seleccionado no existe.',
            'vigencia_dias.integer' => 'La vigencia debe expresarse en días.',
            'vigencia_dias.min' => 'La vigencia debe ser de al menos un día.',
            'vigencia_dias.max' => 'La vigencia no puede superar los 365 días.',

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

            'servicios.*.productos.*.producto_id.required' => 'Seleccione el producto de almacén.',
            'servicios.*.productos.*.producto_id.exists' => 'El producto seleccionado no existe.',
            'servicios.*.productos.*.producto_id.distinct' => 'No repita el mismo producto en un servicio.',
            'servicios.*.productos.*.cantidad.required' => 'Indique la cantidad del producto.',
            'servicios.*.productos.*.cantidad.integer' => 'La cantidad del producto debe ser un número entero.',
            'servicios.*.productos.*.cantidad.min' => 'La cantidad del producto debe ser al menos 1.',
            'servicios.*.productos.*.precio_venta.required' => 'Indique el precio de venta del producto.',
            'servicios.*.productos.*.precio_venta.numeric' => 'El precio de venta debe ser un número.',
            'servicios.*.productos.*.precio_venta.min' => 'El precio de venta no puede ser negativo.',

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

            $id = $linea['id'] ?? null;
            $lineaId = $linea[$clave] ?? null;
            $cantidad = $this->normalizarNumero($linea['cantidad'] ?? null);
            $precio = $this->normalizarNumero($linea['precio_unitario'] ?? null);

            if ($this->lineaVacia($lineaId, $cantidad, $precio)) {
                continue;
            }

            $normalizadas[] = [
                'id' => $id !== null && $id !== '' ? (int) $id : null,
                $clave => $lineaId,
                'cantidad' => $cantidad,
                'precio_unitario' => $precio,
                'productos' => $this->normalizarProductos($linea['productos'] ?? []),
            ];
        }

        return $normalizadas;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizarProductos(mixed $productos): array
    {
        if (! is_array($productos)) {
            return [];
        }

        $normalizados = [];

        foreach ($productos as $producto) {
            if (! is_array($producto)) {
                continue;
            }

            $id = $producto['id'] ?? null;
            $productoId = $producto['producto_id'] ?? null;
            $cantidad = $this->normalizarNumero($producto['cantidad'] ?? null);
            $precio = $this->normalizarNumero($producto['precio_venta'] ?? null);

            if ($this->lineaVacia($productoId, $cantidad, $precio)) {
                continue;
            }

            $normalizados[] = [
                'id' => $id !== null && $id !== '' ? (int) $id : null,
                'producto_id' => $productoId,
                'cantidad' => $cantidad,
                'precio_venta' => $precio,
            ];
        }

        return $normalizados;
    }

    private function lineaVacia(mixed $id, mixed $cantidad, mixed $precio): bool
    {
        return ($id === null || $id === '')
            && ($cantidad === null || $cantidad === '')
            && ($precio === null || $precio === '');
    }

    private function normalizarNumero(mixed $valor): mixed
    {
        if (is_string($valor)) {
            $valor = trim($valor);

            return $valor === '' ? null : str_replace(',', '.', $valor);
        }

        return $valor;
    }

    private function limpiarNumero(mixed $valor): mixed
    {
        if (is_string($valor)) {
            $valor = trim($valor);

            return $valor === '' ? null : $valor;
        }

        return $valor;
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
