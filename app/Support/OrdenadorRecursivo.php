<?php

namespace App\Support;

/**
 * ORDENIA IA — PEA #2: Recursividad.
 *
 * Ordena un arreglo de números (enteros, negativos o decimales) de MAYOR
 * a MENOR usando el algoritmo Quicksort implementado de forma
 * genuinamente recursiva (divide y vencerás), sin apoyarse en las
 * funciones de ordenamiento nativas de PHP (sort/rsort/usort).
 *
 * Esta clase es utilizada realmente por ExerciseGeneratorService para
 * calcular la respuesta_correcta de cada ejercicio y por JuegoController
 * para validar la respuesta enviada por el alumno. No es una función
 * decorativa: si se elimina, el sistema deja de poder generar ni
 * calificar ejercicios.
 */
class OrdenadorRecursivo
{
    /**
     * Punto de entrada público. Devuelve una copia nueva del arreglo
     * ordenada de mayor a menor.
     *
     * @param array<int, float|int> $numeros
     * @return array<int, float|int>
     */
    public static function ordenarDescendente(array $numeros): array
    {
        return self::quicksortDescendente($numeros);
    }

    /**
     * Caso base: un arreglo de 0 o 1 elementos ya está ordenado.
     * Caso recursivo: se elige un pivote, se particiona el resto del
     * arreglo en "mayores o iguales" y "menores" que el pivote, cada
     * partición se ordena llamando de nuevo a esta misma función
     * (recursividad real) y finalmente se combinan los resultados.
     *
     * @param array<int, float|int> $numeros
     * @return array<int, float|int>
     */
    private static function quicksortDescendente(array $numeros): array
    {
        $total = count($numeros);

        // Caso base de la recursión.
        if ($total <= 1) {
            return array_values($numeros);
        }

        $numeros = array_values($numeros);
        $pivote = $numeros[intdiv($total, 2)];

        $mayoresOIguales = [];
        $menores = [];
        $pivoteUsado = false;

        foreach ($numeros as $indice => $valor) {
            if ($indice === intdiv($total, 2) && ! $pivoteUsado) {
                $pivoteUsado = true;
                continue;
            }

            if ($valor >= $pivote) {
                $mayoresOIguales[] = $valor;
            } else {
                $menores[] = $valor;
            }
        }

        // Llamadas recursivas sobre subconjuntos cada vez más pequeños.
        return array_merge(
            self::quicksortDescendente($mayoresOIguales),
            [$pivote],
            self::quicksortDescendente($menores)
        );
    }
}
