<?php

namespace App\Models\Concerns;

use App\Models\HistorialPrecio;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait RegistraHistorialPrecio
{
    /**
     * Historial de cambios de precio de la línea.
     */
    public function historialPrecios(): MorphMany
    {
        return $this->morphMany(HistorialPrecio::class, 'historialable')->latest('id');
    }

    /**
     * Registrar un cambio de precio cuando el valor realmente varía.
     */
    public function registrarCambioPrecio(
        float $precioAnterior,
        float $precioNuevo,
        ?User $usuario = null,
    ): ?HistorialPrecio {
        $anterior = round($precioAnterior, 2);
        $nuevo = round($precioNuevo, 2);

        if (abs($anterior - $nuevo) < 0.005) {
            return null;
        }

        return $this->historialPrecios()->create([
            'precio_anterior' => $anterior,
            'precio_nuevo' => $nuevo,
            'user_id' => $usuario?->id,
        ]);
    }
}
