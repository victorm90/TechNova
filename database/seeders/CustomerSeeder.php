<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientes = [
            [
                'name' => 'Maria Elena Rodriguez',
                'address' => 'Calle 12 #45-18, Centro',
                'phone' => '55501001',
                'identity_card' => '12345678901',
            ],
            [
                'name' => 'Carlos Manuel Perez',
                'address' => 'Avenida Libertad #8-22, Norte',
                'phone' => '55501002',
                'identity_card' => '98765432109',
            ],
        ];

        foreach ($clientes as $cliente) {
            Customer::query()->firstOrCreate(
                ['identity_card' => $cliente['identity_card']],
                $cliente,
            );
        }

        $faltantes = max(0, 10 - Customer::query()->count());

        if ($faltantes > 0) {
            Customer::factory($faltantes)->create();
        }
    }
}
