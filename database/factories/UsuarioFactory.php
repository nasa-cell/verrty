<?php

namespace Database\Factories;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/** @extends Factory<Usuario> */
class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->firstName(),
            'apellido' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'rol' => 'alumno',
            'estado' => 'activo',
        ];
    }

    public function profesor(): static
    {
        return $this->state(fn () => ['rol' => 'profesor']);
    }

    public function administrador(): static
    {
        return $this->state(fn () => ['rol' => 'administrador']);
    }
}
