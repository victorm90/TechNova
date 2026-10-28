<?php

namespace App\Models;

use Database\Factories\ProductoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'sku',
    'nombre',
    'descripcion',
    'categoria_producto_id',
    'unidad_medida_id',
    'imagen',
    'precio_compra',
    'precio_venta',
    'stock_minimo',
    'activo',
])]
class Producto extends Model
{
    /** @use HasFactory<ProductoFactory> */
    use HasFactory;

    protected $table = 'productos';

    /**
     * Categoría a la que pertenece el producto.
     */
    public function categoriaProducto(): BelongsTo
    {
        return $this->belongsTo(CategoriaProducto::class, 'categoria_producto_id');
    }

    /**
     * Unidad de medida del producto.
     */
    public function unidadMedida(): BelongsTo
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_medida_id');
    }

    /**
     * Existencias del producto por almacén.
     */
    public function inventarios(): HasMany
    {
        return $this->hasMany(Inventario::class, 'producto_id');
    }

    /**
     * Líneas de operaciones en las que participa el producto.
     */
    public function lineasOperacion(): HasMany
    {
        return $this->hasMany(OperacionInventarioLinea::class, 'producto_id');
    }

    /**
     * Movimientos auditables del producto.
     */
    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'producto_id');
    }

    /**
     * Limitar la consulta a productos activos.
     */
    public function scopeActivos(Builder $query): void
    {
        $query->where('activo', true);
    }

    /**
     * URL pública de la imagen del producto o del placeholder del sistema.
     */
    public function imagenUrl(): string
    {
        if (is_string($this->imagen) && $this->imagen !== '') {
            return asset('storage/'.$this->imagen);
        }

        return asset('assets/images/no-data.png');
    }

    /**
     * Existencia total del producto sumando todos los almacenes.
     */
    public function stockTotal(): int
    {
        return (int) $this->inventarios()->sum('cantidad');
    }

    /**
     * Determinar si la existencia total está por debajo del mínimo.
     */
    public function tieneStockBajo(): bool
    {
        return $this->stockTotal() <= (int) $this->stock_minimo;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'precio_compra' => 'decimal:2',
            'precio_venta' => 'decimal:2',
            'stock_minimo' => 'integer',
            'activo' => 'boolean',
        ];
    }
}
