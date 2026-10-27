<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'codigo',
    'tipo',
    'almacen_id',
    'proveedor_id',
    'user_id',
    'referencia',
    'notas',
    'total',
])]
class OperacionInventario extends Model
{
    public const TIPO_ENTRADA = 'entrada';

    public const TIPO_SALIDA = 'salida';

    public const TIPO_AJUSTE = 'ajuste';

    protected $table = 'operaciones_inventario';

    /**
     * Líneas de detalle de la operación.
     */
    public function lineas(): HasMany
    {
        return $this->hasMany(OperacionInventarioLinea::class, 'operacion_inventario_id');
    }

    /**
     * Movimientos generados por la operación.
     */
    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'operacion_inventario_id');
    }

    /**
     * Almacén donde se aplicó la operación.
     */
    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_id');
    }

    /**
     * Proveedor asociado a una entrada.
     */
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    /**
     * Usuario que registró la operación.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Indica si la operación es una entrada.
     */
    public function esEntrada(): bool
    {
        return $this->tipo === self::TIPO_ENTRADA;
    }

    /**
     * Cantidad total de unidades de la operación.
     */
    public function cantidadTotal(): int
    {
        return (int) $this->lineas()->sum('cantidad');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
        ];
    }
}
