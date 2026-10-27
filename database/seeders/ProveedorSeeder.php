<?php

namespace Database\Seeders;

use App\Models\Proveedor;
use Illuminate\Database\Seeder;

class ProveedorSeeder extends Seeder
{
    public function run(): void
    {
        $proveedores = [
            [
                'nombre' => 'Suministros Industriales S.A.',
                'contacto' => 'Laura Méndez',
                'telefono' => '55502001',
                'email' => 'ventas@suministros.test',
                'direccion' => 'Polígono Industrial Norte, nave 4',
                'identificacion_fiscal' => 'RIF-J-12345678-9',
                'notas' => 'Entrega en 48 horas para pedidos confirmados.',
            ],
            [
                'nombre' => 'Empaques del Centro',
                'contacto' => 'Jorge Salas',
                'telefono' => '55502002',
                'email' => 'contacto@empaques.test',
                'direccion' => 'Calle del Comercio #22',
                'identificacion_fiscal' => 'RIF-J-87654321-0',
                'notas' => null,
            ],
            [
                'nombre' => 'Equipos y Herramientas del Sur',
                'contacto' => 'Marta Rivas',
                'telefono' => '55502003',
                'email' => 'compras@equiposur.test',
                'direccion' => 'Avenida Sur #140',
                'identificacion_fiscal' => 'RIF-J-11223344-5',
                'notas' => 'Descuento por volumen a partir de 10 unidades.',
            ],
        ];

        foreach ($proveedores as $proveedor) {
            Proveedor::query()->updateOrCreate(
                ['nombre' => $proveedor['nombre']],
                [
                    'contacto' => $proveedor['contacto'],
                    'telefono' => $proveedor['telefono'],
                    'email' => $proveedor['email'],
                    'direccion' => $proveedor['direccion'],
                    'identificacion_fiscal' => $proveedor['identificacion_fiscal'],
                    'notas' => $proveedor['notas'],
                    'activo' => true,
                ],
            );
        }
    }
}
