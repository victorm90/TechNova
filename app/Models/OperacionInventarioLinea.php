<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'operacion_inventario_id',
    'producto_id',
    'cantidad',
    'costo_unitario',
    'subtotal',
])]
class OperacionInventarioLinea extends Model
{
    protected $table = 'operacion_inventario_lineas';

    /**
     * Operación a la que pertenece la línea.
     */
    public function operacion(): BelongsTo
    {
        return $this->belongsTo(OperacionInventario::class, 'operacion_inventario_id');
    }

    /**
     * Producto de la línea.
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
            'costo_unitario' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }
}
