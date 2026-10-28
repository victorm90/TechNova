<?php

namespace App\Models;

use App\Models\Concerns\RegistraHistorialPrecio;
use Database\Factories\OfertaServicioProductoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['oferta_servicio_id', 'producto_id', 'cantidad', 'precio_venta'])]
class OfertaServicioProducto extends Model
{
    /** @use HasFactory<OfertaServicioProductoFactory> */
    use HasFactory;

    use RegistraHistorialPrecio;

    protected $table = 'oferta_servicio_productos';

    /**
     * Línea de servicio que consume el producto.
     */
    public function ofertaServicio(): BelongsTo
    {
        return $this->belongsTo(OfertaServicio::class, 'oferta_servicio_id');
    }

    /**
     * Producto de almacén consumido.
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    /**
     * Importe de la línea (cantidad por precio de venta).
     */
    protected function total(): Attribute
    {
        return Attribute::get(
            fn (): float => round((float) $this->cantidad * (float) $this->precio_venta, 2),
        );
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
            'precio_venta' => 'decimal:2',
        ];
    }
}
