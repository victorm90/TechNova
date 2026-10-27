<?php

namespace App\Models;

use Database\Factories\CategoriaProductoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre', 'descripcion', 'activo'])]
class CategoriaProducto extends Model
{
    /** @use HasFactory<CategoriaProductoFactory> */
    use HasFactory;

    protected $table = 'categorias_productos';

    /**
     * Productos asociados a la categoría.
     */
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'categoria_producto_id');
    }

    /**
     * Limitar la consulta a categorías activas.
     */
    public function scopeActivas(Builder $query): void
    {
        $query->where('activo', true);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }
}
