<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SecuenciaService
{
    /**
     * Obtener el siguiente código consecutivo de una secuencia.
     *
     * La fila de la secuencia se bloquea con lockForUpdate dentro de una
     * transacción para evitar códigos duplicados en accesos concurrentes.
     */
    public function siguiente(string $clave, string $prefijo, int $longitud = 6): string
    {
        return DB::transaction(function () use ($clave, $prefijo, $longitud): string {
            $ahora = now();

            DB::table('secuencias')->insertOrIgnore([
                'clave' => $clave,
                'ultimo_valor' => 0,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);

            $secuencia = DB::table('secuencias')
                ->where('clave', $clave)
                ->lockForUpdate()
                ->first();

            $siguiente = ((int) ($secuencia->ultimo_valor ?? 0)) + 1;

            DB::table('secuencias')
                ->where('clave', $clave)
                ->update([
                    'ultimo_valor' => $siguiente,
                    'updated_at' => $ahora,
                ]);

            return $prefijo.str_pad((string) $siguiente, $longitud, '0', STR_PAD_LEFT);
        });
    }
}
