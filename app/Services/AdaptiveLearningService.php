<?php

namespace App\Services;

use App\Models\RendimientoAlumno;
use App\Models\Respuesta;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

/**
 * ORDENIA IA — orquesta el aprendizaje adaptativo de un alumno:
 * 1. Recalcula sus estadísticas reales (aciertos, errores, tiempo).
 * 2. Ejecuta el árbol de decisiones (DecisionTreeService).
 * 3. Persiste la nueva dificultad interna del alumno.
 *
 * Todo dentro de una transacción (PEA #5) para que un fallo a mitad de
 * camino no deje al alumno con estadísticas actualizadas pero sin
 * decisión registrada, o viceversa.
 */
class AdaptiveLearningService
{
    public function __construct(private readonly DecisionTreeService $arbolDecisiones)
    {
    }

    /**
     * Recalcula rendimiento_alumnos a partir de las respuestas reales
     * guardadas en la tabla "respuestas". Es el equivalente en PHP del
     * procedimiento almacenado sp_actualizar_rendimiento(), usado como
     * respaldo cuando el hosting no permite CREATE PROCEDURE (por
     * ejemplo, planes gratuitos compartidos).
     */
    public function actualizarRendimiento(Usuario $alumno): RendimientoAlumno
    {
        return DB::transaction(function () use ($alumno) {
            $stats = Respuesta::where('alumno_id', $alumno->id)
                ->selectRaw('COUNT(*) as total')
                ->selectRaw('COALESCE(SUM(es_correcta = 1), 0) as aciertos')
                ->selectRaw('COALESCE(SUM(es_correcta = 0), 0) as errores')
                ->selectRaw('COALESCE(AVG(tiempo_segundos), 0) as tiempo_promedio')
                ->selectRaw('COALESCE(SUM(puntaje_obtenido), 0) as puntaje_total')
                ->first();

            $total = (int) $stats->total;
            $aciertos = (int) $stats->aciertos;
            $errores = (int) $stats->errores;
            $porcentaje = $total > 0 ? round(($aciertos / $total) * 100, 2) : 0.0;

            $rendimiento = RendimientoAlumno::firstOrNew(['alumno_id' => $alumno->id]);
            $rendimiento->fill([
                'alumno_id' => $alumno->id,
                'total_ejercicios' => $total,
                'total_aciertos' => $aciertos,
                'total_errores' => $errores,
                'porcentaje_aciertos' => $porcentaje,
                'porcentaje_errores' => round(100 - $porcentaje, 2),
                'tiempo_promedio_segundos' => round((float) $stats->tiempo_promedio, 2),
                'puntaje_total' => (int) $stats->puntaje_total,
                'dificultad_actual' => $rendimiento->dificultad_actual ?: 1,
                'ultima_evaluacion' => now(),
            ]);
            $rendimiento->save();

            $this->actualizarRacha($alumno, $rendimiento);

            return $rendimiento;
        });
    }

    private function actualizarRacha(Usuario $alumno, RendimientoAlumno $rendimiento): void
    {
        $ultimas = Respuesta::where('alumno_id', $alumno->id)
            ->orderByDesc('fecha_respuesta')
            ->limit(50)
            ->pluck('es_correcta');

        $racha = 0;
        foreach ($ultimas as $esCorrecta) {
            if (! $esCorrecta) {
                break;
            }
            $racha++;
        }

        $rendimiento->racha_actual = $racha;
        $rendimiento->mejor_racha = max((int) ($rendimiento->mejor_racha ?? 0), $racha);
        $rendimiento->save();
    }

    private function contarErroresRecientes(Usuario $alumno, int $ultimos = 5): int
    {
        return Respuesta::where('alumno_id', $alumno->id)
            ->orderByDesc('fecha_respuesta')
            ->limit($ultimos)
            ->where('es_correcta', false)
            ->count();
    }

    /**
     * Recalcula el rendimiento y ejecuta el árbol de decisiones para
     * determinar (y persistir) la nueva dificultad interna del alumno.
     */
    public function analizarYDecidir(Usuario $alumno): array
    {
        return DB::transaction(function () use ($alumno) {
            $rendimiento = $this->actualizarRendimiento($alumno);
            $erroresRecientes = $this->contarErroresRecientes($alumno);

            $resultado = $this->arbolDecisiones->evaluar($alumno, $rendimiento, $erroresRecientes);

            $rendimiento->dificultad_actual = $resultado['dificultad_resultante'];
            $rendimiento->save();

            return [
                'rendimiento' => $rendimiento->fresh(),
                'decision' => $resultado['decision'],
                'dificultad_anterior' => $resultado['dificultad_anterior'],
                'dificultad_nueva' => $resultado['dificultad_resultante'],
                'explicacion' => $resultado['explicacion'],
            ];
        });
    }
}
