<?php

namespace App\Http\Requests\Producto;

use App\Models\Producto;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class UpdateProductoRequest extends FormRequest
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
            'precio_compra' => $this->input('precio_compra', 0),
            'precio_venta' => $this->input('precio_venta', 0),
            'stock_minimo' => $this->input('stock_minimo', 0),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => [
                'required',
                'string',
                'min:2',
                'max:120',
                $this->nombreUnico(),
            ],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'categoria_producto_id' => ['required', 'integer', 'exists:categorias_productos,id'],
            'unidad_medida_id' => ['required', 'integer', 'exists:unidad_medidas,id'],
            'precio_compra' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'precio_venta' => ['required', 'numeric', 'min:0', 'gte:precio_compra', 'max:99999999.99'],
            'stock_minimo' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'activo' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max' => 'El nombre no puede superar los 120 caracteres.',
            'nombre.unique' => 'Ya existe un producto con ese nombre en la categoría seleccionada.',
            'categoria_producto_id.required' => 'La categoría es obligatoria.',
            'categoria_producto_id.exists' => 'La categoría seleccionada no es válida.',
            'unidad_medida_id.required' => 'La unidad de medida es obligatoria.',
            'unidad_medida_id.exists' => 'La unidad de medida seleccionada no es válida.',
            'descripcion.max' => 'La descripción no puede superar los 1000 caracteres.',
            'precio_compra.required' => 'El precio de compra es obligatorio.',
            'precio_compra.numeric' => 'El precio de compra debe ser un número.',
            'precio_compra.min' => 'El precio de compra no puede ser negativo.',
            'precio_venta.required' => 'El precio de venta es obligatorio.',
            'precio_venta.numeric' => 'El precio de venta debe ser un número.',
            'precio_venta.min' => 'El precio de venta no puede ser negativo.',
            'precio_venta.gte' => 'El precio de venta debe ser mayor o igual al precio de compra.',
            'stock_minimo.required' => 'El stock mínimo es obligatorio.',
            'stock_minimo.min' => 'El stock mínimo no puede ser negativo.',
        ];
    }

    private function nombreUnico(): Unique
    {
        $producto = $this->route('producto');

        return Rule::unique('productos', 'nombre')
            ->where(fn (Builder $query): Builder => $query->where(
                'categoria_producto_id',
                (int) $this->input('categoria_producto_id'),
            ))
            ->ignore($producto instanceof Producto ? $producto->id : null);
    }
}
