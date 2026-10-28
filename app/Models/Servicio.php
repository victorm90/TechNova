<?php

namespace App\Models;

use Database\Factories\ServicioFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre', 'unidad_medida_id', 'precio_unitario', 'activo'])]
class Servicio extends Model
{
    /** @use HasFactory<ServicioFactory> */
    use HasFactory;

    /**
     * Unidad de medida del servicio.
     */
    public function unidadMedida(): BelongsTo
    {
        return $this->belongsTo(UnidadMedida::class);
    }

    /**
     * Líneas de oferta en las que se utiliza el servicio.
     */
    public function ofertaServicios(): HasMany
    {
        return $this->hasMany(OfertaServicio::class);
    }

    /**
     * Limitar la consulta a servicios activos.
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
