<?php

namespace Database\Seeders;

use App\Models\Municipio;
use App\Models\Provincia;
use Illuminate\Database\Seeder;

class MunicipioSeeder extends Seeder
{
    public function run(): void
    {
        $municipiosPorProvincia = [
            '1' => [
                ['codigo' => '0101', 'nombre' => 'Pinar del Río'],
                ['codigo' => '0102', 'nombre' => 'San Luis'],
                ['codigo' => '0103', 'nombre' => 'Los Palacios'],
                ['codigo' => '0104', 'nombre' => 'Viñales'],
                ['codigo' => '0105', 'nombre' => 'La Palma'],
                ['codigo' => '0106', 'nombre' => 'Sandino'],
            ],
            '2' => [
                ['codigo' => '0201', 'nombre' => 'Artemisa'],
                ['codigo' => '0202', 'nombre' => 'San Cristóbal'],
                ['codigo' => '0203', 'nombre' => 'Mariel'],
                ['codigo' => '0204', 'nombre' => 'Bahía Honda'],
                ['codigo' => '0205', 'nombre' => 'Candelaria'],
                ['codigo' => '0206', 'nombre' => 'La Palma'],
                ['codigo' => '0207', 'nombre' => 'Bauta'],
            ],
            '15' => [
                ['codigo' => '1501', 'nombre' => 'Guantánamo'],
                ['codigo' => '1502', 'nombre' => 'El Salvador'],
                ['codigo' => '1503', 'nombre' => 'Manuel Tames'],
                ['codigo' => '1504', 'nombre' => 'Yateras'],
                ['codigo' => '1505', 'nombre' => 'Baracoa'],
                ['codigo' => '1506', 'nombre' => 'Maisi'],
                ['codigo' => '1507', 'nombre' => 'Imias'],
                ['codigo' => '1508', 'nombre' => 'San Antonio del Sur'],
                ['codigo' => '1509', 'nombre' => 'Caimanera'],
                ['codigo' => '1510', 'nombre' => 'Niceto Perez'],
            ],
        ];

        foreach ($municipiosPorProvincia as $codigoProvincia => $municipios) {
            $provincia = Provincia::where('codigo', $codigoProvincia)->firstOrFail();

            foreach ($municipios as $municipio) {
                Municipio::updateOrCreate(
                    ['codigo' => $municipio['codigo']],
                    [
                        'nombre' => $municipio['nombre'],
                        'provincia_id' => $provincia->id,
                    ],
                );
            }
        }
    }
}
