<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RoleAndUserSeeder extends Seeder
{
    public function run(): void
    {
        // Crear roles del sistema
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $coordRole = Role::firstOrCreate(['name' => 'coordinador']);
        $docenteRole = Role::firstOrCreate(['name' => 'docente']);

        // Usuario Administrador
        $admin = User::firstOrCreate(
            ['email' => 'admin@edumetrics.sv'],
            [
                'name' => 'David Parada (Admin)',
                'password' => Hash::make('Password123!'),
            ]
        );
        $admin->assignRole($adminRole);

        // Usuario Docente de prueba
        $docente = User::firstOrCreate(
            ['email' => 'docente@edumetrics.sv'],
            [
                'name' => 'Prof. Carlos Mendoza',
                'password' => Hash::make('Password123!'),
            ]
        );
        $docente->assignRole($docenteRole);

        // Usuario Coordinador Académico de prueba
        $coordinador = User::firstOrCreate(
            ['email' => 'coordinador@edumetrics.sv'],
            [
                'name' => 'Lic. Elena Martínez (Coordinadora)',
                'password' => Hash::make('Password123!'),
            ]
        );
        $coordinador->assignRole($coordRole);

    }
}