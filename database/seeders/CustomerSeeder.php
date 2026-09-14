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
        Customer::factory()->createMany([
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
        ]);

        Customer::factory(8)->create();
    }
}
