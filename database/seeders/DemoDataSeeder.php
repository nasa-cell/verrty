<?php

namespace Database\Seeders;

use App\Models\Respuesta;
use App\Models\Usuario;
use App\Services\AdaptiveLearningService;
use App\Services\ExerciseGeneratorService;
use App\Support\CalculadoraPuntaje;
use App\Support\OrdenadorRecursivo;
use Illuminate\Database\Seeder;

/**
 * Crea historial de práctica DEMO para los alumnos de prueba, pero
 * ejecutando el pipeline real (ExerciseGeneratorService, la función
 * recursiva de ordenamiento y AdaptiveLearningService). No se escribe
 * ningún porcentaje ni puntaje a mano: todo se calcula igual que lo
 * haría un alumno real jugando, para que el panel del profesor y
 * Verrity muestren cifras genuinas desde el primer arranque.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $generador = app(ExerciseGeneratorService::class);
        $aprendizaje = app(AdaptiveLearningService::class);

        $perfiles = [
            'alumno@ordenia.com' => ['ejercicios' => 12, 'probabilidad_acierto' => 0.8],
            'juan@ordenia.com' => ['ejercicios' => 15, 'probabilidad_acierto' => 0.9],
            'maria@ordenia.com' => ['ejercicios' => 10, 'probabilidad_acierto' => 0.5],
            'carlos@ordenia.com' => ['ejercicios' => 8, 'probabilidad_acierto' => 0.35],
        ];

        foreach ($perfiles as $email => $perfil) {
            $alumno = Usuario::where('email', $email)->first();

            if (! $alumno) {
                continue;
            }

            $dificultad = 1;

            for ($i = 0; $i < $perfil['ejercicios']; $i++) {
                $ejercicio = $generador->generarUno($alumno, $dificultad);

                $vaAAcertar = (mt_rand(1, 100) / 100) <= $perfil['probabilidad_acierto'];
                $respuestaCorrecta = OrdenadorRecursivo::ordenarDescendente($ejercicio->numeros_generados);
                $respuestaUsuario = $vaAAcertar ? $respuestaCorrecta : array_reverse($respuestaCorrecta);
                $tiempo = random_int(6, $ejercicio->tiempo_limite_segundos - 2);
                $puntaje = CalculadoraPuntaje::calcular($vaAAcertar, $tiempo, $ejercicio->tiempo_limite_segundos, $ejercicio->dificultad);

                Respuesta::create([
                    'ejercicio_id' => $ejercicio->id,
                    'alumno_id' => $alumno->id,
                    'respuesta_usuario' => $respuestaUsuario,
                    'es_correcta' => $vaAAcertar,
                    'tiempo_segundos' => $tiempo,
                    'puntaje_obtenido' => $puntaje,
                    'respondido_por_tiempo' => false,
                    'fecha_respuesta' => now()->subDays(random_int(0, 6))->subMinutes(random_int(0, 500)),
                ]);

                $ejercicio->estado = 'respondido';
                $ejercicio->fecha_inicio = $ejercicio->fecha_generacion;
                $ejercicio->fecha_fin = now();
                $ejercicio->save();

                $resultado = $aprendizaje->analizarYDecidir($alumno);
                $dificultad = $resultado['dificultad_nueva'];
            }
        }
    }
}
