<?php

namespace App\Models;

use Database\Factories\OfertaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['codigo', 'customer_id', 'fecha', 'estado'])]
class Oferta extends Model
{
    /** @use HasFactory<OfertaFactory> */
    use HasFactory;

    public const ESTADO_BORRADOR = 'borrador';

    public const ESTADO_ENVIADA = 'enviada';

    public const ESTADO_ACEPTADA = 'aceptada';

    public const ESTADO_RECHAZADA = 'rechazada';

    /**
     * Estados disponibles de una oferta.
     *
     * @return array<string, string>
     */
    public static function estados(): array
    {
        return [
            self::ESTADO_BORRADOR => 'Borrador',
            self::ESTADO_ENVIADA => 'Enviada',
            self::ESTADO_ACEPTADA => 'Aceptada',
            self::ESTADO_RECHAZADA => 'Rechazada',
        ];
    }

    /**
     * Cliente al que se dirige la oferta.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Servicios incluidos en la oferta.
     */
    public function servicios(): HasMany
    {
        return $this->hasMany(OfertaServicio::class);
    }

    /**
     * Gastos incluidos en la oferta.
     */
    public function gastos(): HasMany
    {
        return $this->hasMany(OfertaGasto::class);
    }

    /**
     * Nombre visible del estado.
     */
    public function estadoLabel(): string
    {
        return self::estados()[$this->estado] ?? ucfirst($this->estado);
    }

    /**
     * Estado equivalente para el componente de insignias.
     */
    public function estadoBadge(): string
    {
        return match ($this->estado) {
            self::ESTADO_BORRADOR => 'draft',
            self::ESTADO_ENVIADA => 'pending',
            self::ESTADO_ACEPTADA => 'active',
            default => 'cancelled',
        };
    }

    /**
     * Importe total de los servicios de la oferta.
     */
    protected function totalServicios(): Attribute
    {
        return Attribute::get(
            fn (): float => round((float) $this->servicios->sum('total'), 2),
        );
    }

    /**
     * Importe total de los gastos de la oferta.
     */
    protected function totalGastos(): Attribute
    {
        return Attribute::get(
            fn (): float => round((float) $this->gastos->sum('total'), 2),
        );
    }

    /**
     * Importe total de la oferta.
     */
    protected function total(): Attribute
    {
        return Attribute::get(
            fn (): float => round($this->total_servicios + $this->total_gastos, 2),
        );
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }
}
