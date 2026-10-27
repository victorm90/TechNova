<?php

namespace App\Models;

use Database\Factories\AlmacenFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre', 'direccion', 'telefono', 'activo'])]
class Almacen extends Model
{
    /** @use HasFactory<AlmacenFactory> */
    use HasFactory;

    protected $table = 'almacenes';

    /**
     * Existencias registradas en el almacén.
     */
    public function inventarios(): HasMany
    {
        return $this->hasMany(Inventario::class, 'almacen_id');
    }

    /**
     * Operaciones de entrada y salida del almacén.
     */
    public function operaciones(): HasMany
    {
        return $this->hasMany(OperacionInventario::class, 'almacen_id');
    }

    /**
     * Limitar la consulta a almacenes activos.
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
            'activo' => 'boolean',
        ];
    }
}
