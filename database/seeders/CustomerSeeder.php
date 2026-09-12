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
                'phone' => '555-0101',
                'identity_card' => '12345678901',
            ],
            [
                'name' => 'Carlos Manuel Perez',
                'address' => 'Avenida Libertad #8-22, Norte',
                'phone' => '555-0102',
                'identity_card' => '98765432109',
            ],
        ]);

        Customer::factory(8)->create();
    }
}
