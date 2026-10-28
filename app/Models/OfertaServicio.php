<?php

namespace App\Models;

use App\Models\Concerns\RegistraHistorialPrecio;
use Database\Factories\OfertaServicioFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['oferta_id', 'servicio_id', 'cantidad', 'precio_unitario'])]
class OfertaServicio extends Model
{
    /** @use HasFactory<OfertaServicioFactory> */
    use HasFactory;

    use RegistraHistorialPrecio;

    /**
     * Oferta a la que pertenece la línea.
     */
    public function oferta(): BelongsTo
    {
        return $this->belongsTo(Oferta::class);
    }

    /**
     * Servicio de catálogo utilizado en la línea.
     */
    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }

    /**
     * Productos de almacén consumidos por este servicio.
     */
    public function productos(): HasMany
    {
        return $this->hasMany(OfertaServicioProducto::class);
    }

    /**
     * Importe de los productos consumidos por la línea.
     */
    protected function totalProductos(): Attribute
    {
        return Attribute::get(
            fn (): float => round((float) $this->productos->sum('total'), 2),
        );
    }

    /**
     * Importe de la línea (cantidad por precio unitario).
     */
    protected function total(): Attribute
    {
        return Attribute::get(
            fn (): float => round((float) $this->cantidad * (float) $this->precio_unitario, 2),
        );
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
            'precio_unitario' => 'decimal:2',
        ];
    }
}
