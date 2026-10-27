<?php

namespace App\Models;

use Database\Factories\UnidadMedidaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre', 'abreviatura'])]
class UnidadMedida extends Model
{
    /** @use HasFactory<UnidadMedidaFactory> */
    use HasFactory;

    /**
     * Servicios que usan la unidad de medida.
     */
    public function servicios(): HasMany
    {
        return $this->hasMany(Servicio::class, 'unidad_medida_id');
    }

    /**
     * Productos que usan la unidad de medida.
     */
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'unidad_medida_id');
    }
}
