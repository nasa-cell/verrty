<?php

namespace Database\Factories;

use App\Models\Ejercicio;
use App\Models\Usuario;
use App\Support\OrdenadorRecursivo;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Ejercicio> */
class EjercicioFactory extends Factory
{
    protected $model = Ejercicio::class;

    public function definition(): array
    {
        $cantidad = fake()->numberBetween(3, 10);
        $numeros = fake()->randomElements(range(-50, 99), $cantidad);

        return [
            'alumno_id' => Usuario::factory(),
            'profesor_id' => null,
            'numeros_generados' => $numeros,
            'respuesta_correcta' => OrdenadorRecursivo::ordenarDescendente($numeros),
            'cantidad_numeros' => $cantidad,
            'dificultad' => fake()->numberBetween(1, 10),
            'tipo_numeros' => 'positivos',
            'generado_por_ia' => true,
            'estado' => 'pendiente',
            'tiempo_limite_segundos' => 30,
            'fecha_generacion' => now(),
        ];
    }
}
