<?php

namespace App\Models;

use Database\Factories\OfertaGastoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['oferta_id', 'gasto_id', 'cantidad', 'precio_unitario'])]
class OfertaGasto extends Model
{
    /** @use HasFactory<OfertaGastoFactory> */
    use HasFactory;

    /**
     * Oferta a la que pertenece la línea.
     */
    public function oferta(): BelongsTo
    {
        return $this->belongsTo(Oferta::class);
    }

    /**
     * Gasto de catálogo utilizado en la línea.
     */
    public function gasto(): BelongsTo
    {
        return $this->belongsTo(Gasto::class);
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
