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
            'categoria_producto_id' => ['required', 'integer', 'exists:categorias_productos,id'],
            'unidad_medida_id' => ['required', 'integer', 'exists:unidad_medidas,id'],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
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
            'imagen.image' => 'El archivo debe ser una imagen.',
            'imagen.mimes' => 'La imagen debe ser de tipo JPG, JPEG, PNG o WEBP.',
            'imagen.max' => 'La imagen no puede superar los 2 MB.',
            'imagen.uploaded' => 'No se pudo subir la imagen. Inténtelo nuevamente.',
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
