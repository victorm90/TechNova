<?php

namespace App\Models;

use Database\Factories\GastoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre', 'precio_unitario', 'activo'])]
class Gasto extends Model
{
    /** @use HasFactory<GastoFactory> */
    use HasFactory;

    /**
     * Líneas de oferta en las que se utiliza el gasto.
     */
    public function ofertaGastos(): HasMany
    {
        return $this->hasMany(OfertaGasto::class);
    }

    /**
     * Limitar la consulta a gastos activos.
     */
    public function scopeActivos(Builder $query): void
    {
        $query->where('activo', true);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'precio_unitario' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }
}
