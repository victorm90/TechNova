<?php

namespace App\Models;

use Database\Factories\InventarioFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'producto_id',
    'almacen_id',
    'cantidad',
    'costo_promedio',
    'ultimo_movimiento_at',
])]
class Inventario extends Model
{
    /** @use HasFactory<InventarioFactory> */
    use HasFactory;

    protected $table = 'inventarios';

    /**
     * Producto al que corresponde la existencia.
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /**
     * Almacén donde se encuentra la existencia.
     */
    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_id');
    }

    /**
     * Valor total de la existencia según el costo promedio.
     */
    public function valorTotal(): float
    {
        return (float) $this->cantidad * (float) $this->costo_promedio;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
            'costo_promedio' => 'decimal:4',
            'ultimo_movimiento_at' => 'datetime',
        ];
    }
}
