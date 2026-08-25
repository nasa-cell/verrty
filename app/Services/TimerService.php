<?php

namespace App\Services;

use App\Models\Ejercicio;
use App\Models\Respuesta;
use Illuminate\Support\Facades\DB;

/**
 * ORDENIA IA — PEA #7: temporizador y procesamiento en segundo plano.
 *
 * El tiempo real de un ejercicio siempre se calcula a partir de
 * "fecha_inicio" guardada en la base de datos, nunca de un contador en
 * el navegador. Esto soluciona dos problemas reales:
 *
 * 1. Si el alumno recarga la página, el temporizador visual debe
 *    mostrar el tiempo que REALMENTE queda, no reiniciar desde el
 *    máximo — antes se reiniciaba porque la vista siempre mostraba
 *    tiempo_limite_segundos completo sin restar lo ya transcurrido.
 * 2. Si por alguna razón el worker de colas (ExpirarEjercicioJob) no
 *    está corriendo, este servicio actúa como respaldo síncrono: cada
 *    vez que el alumno abre /alumno/jugar se revisa si el ejercicio
 *    pendiente ya debió vencer, y si es así se vence en el momento.
 */
class TimerService
{
    public function __construct(private readonly AdaptiveLearningService $aprendizaje)
    {
    }

    public function segundosTranscurridos(Ejercicio $ejercicio): int
    {
        if (! $ejercicio->fecha_inicio) {
            return 0;
        }

        // OJO: en Carbon 3 (Laravel 12), diffInSeconds() ya NO devuelve
        // siempre un valor absoluto por defecto — si la fecha dada es
        // pasada, devuelve un número NEGATIVO. Sin el "true" aquí, el
        // max(0, ...) de abajo recortaba ese negativo a 0 siempre y el
        // tiempo transcurrido nunca avanzaba (el bug real del
        // temporizador que "se reiniciaba" al recargar la página).
        // También devuelve float (con microsegundos) desde Carbon 3,
        // por eso el (int) explícito antes de max().
        return max(0, (int) now()->diffInSeconds($ejercicio->fecha_inicio, true));
    }

    public function segundosRestantes(Ejercicio $ejercicio): int
    {
        return max(0, $ejercicio->tiempo_limite_segundos - $this->segundosTranscurridos($ejercicio));
    }

    public function haVencido(Ejercicio $ejercicio): bool
    {
        return $ejercicio->fecha_inicio !== null
            && $this->segundosTranscurridos($ejercicio) >= $ejercicio->tiempo_limite_segundos;
    }

    /**
     * Si el ejercicio ya venció y sigue "pendiente", lo marca "vencido",
     * registra una respuesta vacía con puntaje 0 y actualiza el
     * rendimiento del alumno — la misma lógica que ExpirarEjercicioJob,
     * pero ejecutada de inmediato (sin depender de un worker de colas).
     * Devuelve true si efectivamente lo venció.
     */
    public function vencerSiCorresponde(Ejercicio $ejercicio): bool
    {
        if ($ejercicio->estado !== 'pendiente' || ! $this->haVencido($ejercicio)) {
            return false;
        }

        $vencido = DB::transaction(function () use ($ejercicio) {
            $actual = Ejercicio::lockForUpdate()->find($ejercicio->id);

            if (! $actual || $actual->estado !== 'pendiente') {
                return false;
            }

            $actual->estado = 'vencido';
            $actual->fecha_fin = now();
            $actual->save();

            Respuesta::create([
                'ejercicio_id' => $actual->id,
                'alumno_id' => $actual->alumno_id,
                'respuesta_usuario' => [],
                'es_correcta' => false,
                'tiempo_segundos' => $actual->tiempo_limite_segundos,
                'puntaje_obtenido' => 0,
                'respondido_por_tiempo' => true,
                'fecha_respuesta' => now(),
            ]);

            $this->aprendizaje->actualizarRendimiento($actual->alumno);

            return true;
        });

        if ($vencido) {
            $ejercicio->estado = 'vencido';
        }

        return $vencido;
    }
}
