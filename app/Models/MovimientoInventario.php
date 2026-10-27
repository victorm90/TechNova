<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'operacion_inventario_id',
    'producto_id',
    'almacen_id',
    'tipo',
    'cantidad',
    'saldo_resultante',
    'costo_unitario',
    'user_id',
])]
class MovimientoInventario extends Model
{
    protected $table = 'movimientos_inventario';

    /**
     * Operación que originó el movimiento.
     */
    public function operacion(): BelongsTo
    {
        return $this->belongsTo(OperacionInventario::class, 'operacion_inventario_id');
    }

    /**
     * Producto afectado.
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /**
     * Almacén afectado.
     */
    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_id');
    }

    /**
     * Usuario responsable del movimiento.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
            'saldo_resultante' => 'integer',
            'costo_unitario' => 'decimal:4',
        ];
    }
}
