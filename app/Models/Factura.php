<?php

namespace App\Models;

use Database\Factories\FacturaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'codigo',
    'oferta_id',
    'customer_id',
    'fecha',
    'subtotal_servicios',
    'subtotal_gastos',
    'subtotal_productos',
    'total',
    'nota',
    'forma_pago',
    'observaciones',
    'firma_pie',
    'almacen_id',
    'user_id',
])]
class Factura extends Model
{
    /** @use HasFactory<FacturaFactory> */
    use HasFactory;

    protected $table = 'facturas';

    /**
     * Oferta que originó la factura.
     */
    public function oferta(): BelongsTo
    {
        return $this->belongsTo(Oferta::class);
    }

    /**
     * Cliente facturado.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Líneas congeladas de la factura.
     */
    public function lineas(): HasMany
    {
        return $this->hasMany(FacturaLinea::class)->orderBy('orden');
    }

    /**
     * Almacén del que se descontaron los productos.
     */
    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class);
    }

    /**
     * Usuario que facturó la oferta.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Operaciones de inventario generadas por la factura.
     */
    public function operacionesInventario(): HasMany
    {
        return $this->hasMany(OperacionInventario::class, 'factura_id');
    }

    /**
     * Líneas de un tipo concreto (servicio, gasto o producto).
     *
     * @return Collection<int, FacturaLinea>
     */
    public function lineasDe(string $tipo): Collection
    {
        return $this->lineas->where('tipo', $tipo)->values();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'subtotal_servicios' => 'decimal:2',
            'subtotal_gastos' => 'decimal:2',
            'subtotal_productos' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }
}
