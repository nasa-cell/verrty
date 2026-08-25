<?php

namespace App\Services;

use App\Models\DecisionIa;
use App\Models\RendimientoAlumno;
use App\Models\Usuario;

/**
 * ORDENIA IA — PEA #3: Árbol de decisiones.
 *
 *                      ¿PORCENTAJE DE ACIERTOS?
 *
 *          >= 85%           60% - 84%            < 60%
 *            |                  |                   |
 *      SUBIR DIFICULTAD   MANTENER DIFICULTAD   BAJAR DIFICULTAD
 *            |                  |                   |
 *    ¿tiempo promedio     (sin más ramas)      ¿errores recientes
 *     muy alto?                                 en aumento?
 *      /        \                                 /        \
 *   sí (no      no (sube                       sí (baja   no (baja
 *   sube más)    con normalidad)                2 niveles)  1 nivel)
 *
 * Cada decisión se documenta con su explicación y se guarda en la
 * tabla decisiones_ia como bitácora auditable de la IA de Verrity.
 */
class DecisionTreeService
{
    private const TIEMPO_ALTO_SEGUNDOS = 25;

    public function __construct(private readonly AIConfigurationManager $configuracionIa)
    {
    }

    /**
     * Evalúa el árbol de decisiones para un alumno y devuelve un arreglo con:
     * decision, dificultad_resultante, explicacion.
     */
    public function evaluar(Usuario $alumno, RendimientoAlumno $rendimiento, int $erroresRecientes = 0): array
    {
        $porcentaje = (float) $rendimiento->porcentaje_aciertos;
        $tiempoPromedio = (float) $rendimiento->tiempo_promedio_segundos;
        $dificultadActual = (int) $rendimiento->dificultad_actual;

        $min = $this->configuracionIa->dificultadMinima();
        $max = $this->configuracionIa->dificultadMaxima();

        if ($porcentaje >= $this->configuracionIa->porcentajeSubir()) {
            // Rama >= 85%: en principio SUBE, salvo que el tiempo promedio
            // sea demasiado alto (el alumno acierta, pero muy lento: no
            // conviene subir la dificultad todavía).
            if ($tiempoPromedio > self::TIEMPO_ALTO_SEGUNDOS) {
                $decision = 'mantener_dificultad';
                $nueva = $dificultadActual;
                $explicacion = sprintf(
                    'Precisión alta (%.2f%%) pero el tiempo promedio (%.1fs) es elevado. '.
                    'Se mantiene la dificultad para consolidar velocidad antes de subir.',
                    $porcentaje,
                    $tiempoPromedio
                );
            } else {
                $decision = 'subir_dificultad';
                $nueva = min($max, $dificultadActual + 1);
                $explicacion = sprintf(
                    'Precisión alta (%.2f%%) con buen tiempo promedio (%.1fs). Se aumenta la dificultad de %d a %d.',
                    $porcentaje,
                    $tiempoPromedio,
                    $dificultadActual,
                    $nueva
                );
            }
        } elseif ($porcentaje >= $this->configuracionIa->porcentajeMantener()) {
            // Rama 60%-84%: se mantiene la dificultad.
            $decision = 'mantener_dificultad';
            $nueva = $dificultadActual;
            $explicacion = sprintf(
                'Precisión intermedia (%.2f%%). Se mantiene la dificultad en %d para consolidar el aprendizaje.',
                $porcentaje,
                $dificultadActual
            );
        } else {
            // Rama < 60%: BAJA. Si además los errores recientes van en
            // aumento, se baja 2 niveles en vez de 1 para no frustrar al alumno.
            if ($erroresRecientes >= 3) {
                $decision = 'bajar_dificultad';
                $nueva = max($min, $dificultadActual - 2);
                $explicacion = sprintf(
                    'Precisión baja (%.2f%%) y %d errores recientes seguidos. Se reduce la dificultad de %d a %d.',
                    $porcentaje,
                    $erroresRecientes,
                    $dificultadActual,
                    $nueva
                );
            } else {
                $decision = 'bajar_dificultad';
                $nueva = max($min, $dificultadActual - 1);
                $explicacion = sprintf(
                    'Precisión baja (%.2f%%). Se reduce la dificultad de %d a %d para reforzar la base.',
                    $porcentaje,
                    $dificultadActual,
                    $nueva
                );
            }
        }

        $registro = DecisionIa::create([
            'alumno_id' => $alumno->id,
            'porcentaje_aciertos' => $porcentaje,
            'tiempo_promedio' => $tiempoPromedio,
            'errores' => $erroresRecientes,
            'dificultad_anterior' => $dificultadActual,
            'decision' => $decision,
            'dificultad_resultante' => $nueva,
            'explicacion' => $explicacion,
            'fecha_decision' => now(),
        ]);

        return [
            'decision' => $decision,
            'dificultad_anterior' => $dificultadActual,
            'dificultad_resultante' => $nueva,
            'explicacion' => $explicacion,
            'registro' => $registro,
        ];
    }
}
