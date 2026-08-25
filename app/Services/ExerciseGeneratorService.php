<?php

namespace App\Services;

use App\Models\Ejercicio;
use App\Models\Usuario;
use App\Support\OrdenadorRecursivo;
use Illuminate\Support\Facades\DB;

/**
 * ORDENIA IA — Genera ejercicios de "ordenar de mayor a menor" a partir
 * de la dificultad interna (1 a 10) decidida por DecisionTreeService.
 * Nunca se muestra "Nivel X" al alumno: la dificultad solo determina
 * cuántos números se generan y de qué tipo son.
 */
class ExerciseGeneratorService
{
    /**
     * Genera y guarda $cantidadEjercicios ejercicios para un alumno,
     * dentro de una única transacción: si uno falla, no se guarda ninguno.
     *
     * @return array<int, Ejercicio>
     */
    public function generarParaAlumno(Usuario $alumno, int $dificultad, ?Usuario $profesor, int $cantidadEjercicios = 10): array
    {
        return DB::transaction(function () use ($alumno, $dificultad, $profesor, $cantidadEjercicios) {
            $ejercicios = [];

            for ($i = 0; $i < $cantidadEjercicios; $i++) {
                $ejercicios[] = $this->generarUno($alumno, $dificultad, $profesor);
            }

            return $ejercicios;
        });
    }

    public function generarUno(Usuario $alumno, int $dificultad, ?Usuario $profesor = null): Ejercicio
    {
        [$cantidad, $tipo] = $this->parametrosPorDificultad($dificultad);

        $numeros = $this->generarNumeros($cantidad, $tipo);

        // Recursividad real (PEA #2): se usa para calcular la respuesta correcta.
        $respuestaCorrecta = OrdenadorRecursivo::ordenarDescendente($numeros);

        return Ejercicio::create([
            'alumno_id' => $alumno->id,
            'profesor_id' => $profesor?->id,
            'numeros_generados' => $numeros,
            'respuesta_correcta' => $respuestaCorrecta,
            'cantidad_numeros' => $cantidad,
            'dificultad' => $dificultad,
            'tipo_numeros' => $tipo,
            'generado_por_ia' => true,
            'estado' => 'pendiente',
            'tiempo_limite_segundos' => 30,
            'fecha_generacion' => now(),
        ]);
    }

    /**
     * @return array{0: int, 1: string} [cantidad_numeros, tipo_numeros]
     */
    private function parametrosPorDificultad(int $dificultad): array
    {
        return match (true) {
            $dificultad <= 2 => [random_int(3, 4), 'positivos'],
            $dificultad <= 5 => [random_int(5, 7), 'positivos'],
            $dificultad <= 7 => [random_int(8, 10), 'positivos'],
            $dificultad <= 9 => [random_int(8, 10), 'negativos'],
            default => [random_int(8, 10), 'decimales'],
        };
    }

    /**
     * @return array<int, int|float>
     */
    private function generarNumeros(int $cantidad, string $tipo): array
    {
        $numeros = [];

        while (count($numeros) < $cantidad) {
            $numero = match ($tipo) {
                'positivos' => random_int(1, 99),
                'negativos', 'mixtos' => random_int(-50, 99),
                'decimales' => $this->numeroDecimalAleatorio(),
                default => random_int(1, 99),
            };

            if (in_array($numero, $numeros, true)) {
                continue; // evita duplicados para que el ejercicio tenga una única solución
            }

            $numeros[] = $numero;
        }

        return $numeros;
    }

    private function numeroDecimalAleatorio(): float
    {
        $entero = random_int(-50, 99);
        $decimal = random_int(1, 9) / 10;

        return round($entero + ($entero >= 0 ? $decimal : -$decimal), 1);
    }
}
