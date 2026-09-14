<?php

namespace Database\Seeders;

use App\Models\Entidad;
use App\Models\Provincia;
use Illuminate\Database\Seeder;

class EntidadSeeder extends Seeder
{
    public function run(): void
    {
        $entidades = [
            [
                'nombre' => 'TechNova',
                'direccion' => 'Calle Pedro A.Perez / 2 y 3 Sur No. 6789, Guantánamo',
                'provincia_codigo' => '15',
                'numero_cuenta' => '100000000000001',
                'telefono' => '48712345',
            ],
            
        ];

        foreach ($entidades as $datosEntidad) {
            $provincia = Provincia::where('codigo', $datosEntidad['provincia_codigo'])->firstOrFail();

            Entidad::updateOrCreate(
                ['nombre' => $datosEntidad['nombre']],
                [
                    'direccion' => $datosEntidad['direccion'],
                    'provincia_id' => $provincia->id,
                    'numero_cuenta' => $datosEntidad['numero_cuenta'],
                    'telefono' => $datosEntidad['telefono'],
                ],
            );
        }
    }
}
