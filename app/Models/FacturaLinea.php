<?php

namespace App\Models;

use Database\Factories\FacturaLineaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'factura_id',
    'tipo',
    'descripcion',
    'cantidad',
    'precio_unitario',
    'subtotal',
    'servicio_id',
    'gasto_id',
    'producto_id',
    'orden',
])]
class FacturaLinea extends Model
{
    public const TIPO_SERVICIO = 'servicio';

    public const TIPO_GASTO = 'gasto';

    public const TIPO_PRODUCTO = 'producto';

    /** @use HasFactory<FacturaLineaFactory> */
    use HasFactory;

    protected $table = 'factura_lineas';

    /**
     * Factura a la que pertenece la línea.
     */
    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class);
    }

    /**
     * Servicio de catálogo referenciado (si aplica).
     */
    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }

    /**
     * Gasto de catálogo referenciado (si aplica).
     */
    public function gasto(): BelongsTo
    {
        return $this->belongsTo(Gasto::class);
    }

    /**
     * Producto de almacén referenciado (si aplica).
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
            'precio_unitario' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'orden' => 'integer',
        ];
    }
}
