<?php

namespace App\Support;

/**
 * Replica en PHP la función SQL fn_calcular_puntaje() (ver migración
 * 2024_01_01_000140) para que el backend pueda calcular el puntaje sin
 * depender de que el motor de base de datos permita funciones
 * almacenadas (algunos hostings gratuitos compartidos no lo permiten).
 */
class CalculadoraPuntaje
{
    public static function calcular(bool $esCorrecta, int $tiempoSegundos, int $tiempoLimiteSegundos, int $dificultad): int
    {
        if (! $esCorrecta) {
            return 0;
        }

        $base = 100;
        $bonus = 0;

        if ($tiempoLimiteSegundos > 0) {
            $bonus = max(0, (int) round((($tiempoLimiteSegundos - $tiempoSegundos) / $tiempoLimiteSegundos) * 50));
        }

        return (int) (($base * $dificultad / 5) + $bonus);
    }
}
