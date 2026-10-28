<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ProvinciaSeeder::class,
            MunicipioSeeder::class,
            EntidadSeeder::class,
            UnidadMedidaSeeder::class,
            CategoriaProductoSeeder::class,
            AlmacenSeeder::class,
            ProveedorSeeder::class,
            ServicioSeeder::class,
            GastoSeeder::class,
            ProductoSeeder::class,
        ]);

        $this->call(CustomerSeeder::class);
    }
}
