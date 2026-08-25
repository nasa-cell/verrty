<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Usuarios de prueba pedidos por el requerimiento: PROFESOR, ALUMNO y
 * ADMINISTRADOR, con contraseñas hasheadas de verdad (Hash::make),
 * nunca en texto plano ni con el placeholder del script SQL original.
 */
class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        Usuario::firstOrCreate(
            ['email' => 'profesor@ordenia.com'],
            [
                'nombre' => 'Profesor',
                'apellido' => 'Demo',
                'password' => Hash::make('profesor123'),
                'rol' => 'profesor',
                'estado' => 'activo',
            ]
        );

        Usuario::firstOrCreate(
            ['email' => 'alumno@ordenia.com'],
            [
                'nombre' => 'Alumno',
                'apellido' => 'Demo',
                'password' => Hash::make('alumno123'),
                'rol' => 'alumno',
                'estado' => 'activo',
            ]
        );

        Usuario::firstOrCreate(
            ['email' => 'admin@ordenia.com'],
            [
                'nombre' => 'Administrador',
                'apellido' => 'Demo',
                'password' => Hash::make('admin123'),
                'rol' => 'administrador',
                'estado' => 'activo',
            ]
        );

        // Alumnos adicionales para poder demostrar ranking y comparaciones.
        $adicionales = [
            ['nombre' => 'Juan', 'apellido' => 'Pérez', 'email' => 'juan@ordenia.com'],
            ['nombre' => 'María', 'apellido' => 'Gómez', 'email' => 'maria@ordenia.com'],
            ['nombre' => 'Carlos', 'apellido' => 'Ruiz', 'email' => 'carlos@ordenia.com'],
        ];

        foreach ($adicionales as $alumno) {
            Usuario::firstOrCreate(
                ['email' => $alumno['email']],
                [
                    'nombre' => $alumno['nombre'],
                    'apellido' => $alumno['apellido'],
                    'password' => Hash::make('alumno123'),
                    'rol' => 'alumno',
                    'estado' => 'activo',
                ]
            );
        }
    }
}
