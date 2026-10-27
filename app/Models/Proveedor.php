<?php

namespace App\Models;

use Database\Factories\ProveedorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'nombre',
    'contacto',
    'telefono',
    'email',
    'direccion',
    'identificacion_fiscal',
    'notas',
    'activo',
])]
class Proveedor extends Model
{
    /** @use HasFactory<ProveedorFactory> */
    use HasFactory;

    protected $table = 'proveedores';

    /**
     * Entradas de inventario suministradas por el proveedor.
     */
    public function operaciones(): HasMany
    {
        return $this->hasMany(OperacionInventario::class, 'proveedor_id');
    }

    /**
     * Limitar la consulta a proveedores activos.
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
