<?php

namespace App\Models;

use Database\Factories\HistorialPrecioFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['precio_anterior', 'precio_nuevo', 'user_id'])]
class HistorialPrecio extends Model
{
    /** @use HasFactory<HistorialPrecioFactory> */
    use HasFactory;

    protected $table = 'historial_precios';

    /**
     * Línea cuyo precio cambió (servicio, gasto o producto).
     */
    public function historialable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Usuario que realizó el cambio.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Descripción legible de la línea afectada.
     */
    public function descripcionLinea(): string
    {
        return match (true) {
            $this->historialable instanceof OfertaServicio => $this->historialable->servicio?->nombre
                ?? 'Servicio eliminado',
            $this->historialable instanceof OfertaGasto => $this->historialable->gasto?->nombre
                ?? 'Gasto eliminado',
            $this->historialable instanceof OfertaServicioProducto => $this->historialable->producto?->nombre
                ?? 'Producto eliminado',
            default => 'Línea eliminada',
        };
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'precio_anterior' => 'decimal:2',
            'precio_nuevo' => 'decimal:2',
        ];
    }
}
