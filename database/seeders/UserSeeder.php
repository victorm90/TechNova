<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Crear los usuarios de desarrollo con sus roles.
     *
     * Estas credenciales son solo para entornos locales. En producción los
     * usuarios deben crearse y asignarse por un proceso administrativo.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            return;
        }

        $admin = User::query()->firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Administrador TechNova',
                'password' => Hash::make('password'),
            ],
        );

        $admin->forceFill([
            'name' => $admin->name === 'Test User' ? 'Administrador TechNova' : $admin->name,
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => $admin->email_verified_at ?? now(),
        ])->save();

        $operator = User::query()->firstOrCreate(
            ['email' => 'operador@technova.test'],
            [
                'name' => 'Operador TechNova',
                'password' => Hash::make('password'),
            ],
        );

        $operator->forceFill([
            'role' => User::ROLE_OPERATOR,
            'email_verified_at' => $operator->email_verified_at ?? now(),
        ])->save();
    }
}
