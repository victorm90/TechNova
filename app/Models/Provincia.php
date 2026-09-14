<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre', 'codigo'])]
class Provincia extends Model
{
    /**
     * Get the municipios for the provincia.
     */
    public function municipios(): HasMany
    {
        return $this->hasMany(Municipio::class);
    }

    /**
     * Get the entidades for the provincia.
     */
    public function entidades(): HasMany
    {
        return $this->hasMany(Entidad::class);
    }
}
