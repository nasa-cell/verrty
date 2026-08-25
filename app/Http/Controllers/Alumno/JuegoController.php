<?php

namespace App\Http\Controllers\Alumno;

use App\Http\Controllers\Controller;
use App\Jobs\ExpirarEjercicioJob;
use App\Models\Respuesta;
use App\Repositories\EjercicioRepository;
use App\Services\AdaptiveLearningService;
use App\Services\ExerciseGeneratorService;
use App\Services\TimerService;
use App\Support\CalculadoraPuntaje;
use App\Support\OrdenadorRecursivo;
use App\Support\VerrityPhraseBank;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class JuegoController extends Controller
{
    public function __construct(
        private readonly EjercicioRepository $ejercicios,
        private readonly ExerciseGeneratorService $generador,
        private readonly AdaptiveLearningService $aprendizaje,
        private readonly TimerService $temporizador,
    ) {
    }

    public function mostrar(): View
    {
        $alumno = Auth::user();
        $ejercicio = $this->ejercicios->siguientePendiente($alumno);

        // Respaldo síncrono: si el ejercicio pendiente ya debió vencer
        // (por ejemplo, porque no hay un worker de colas corriendo para
        // procesar ExpirarEjercicioJob), se vence aquí mismo en vez de
        // dejar que el alumno siga viéndolo como si nada.
        if ($ejercicio && $this->temporizador->vencerSiCorresponde($ejercicio)) {
            $ejercicio = null;
        }

        if (! $ejercicio) {
            $dificultad = $alumno->rendimiento?->dificultad_actual ?? 1;
            $ejercicio = $this->generador->generarUno($alumno, $dificultad);
        }

        if (! $ejercicio->fecha_inicio) {
            $this->ejercicios->marcarInicio($ejercicio);
            // PEA #7: procesamiento en segundo plano — si el alumno no
            // responde antes de que se cumpla el tiempo límite, este Job
            // vence el ejercicio automáticamente sin intervención del navegador.
            ExpirarEjercicioJob::dispatch($ejercicio->id)->delay(now()->addSeconds($ejercicio->tiempo_limite_segundos + 2));
        }

        $numerosDesordenados = $ejercicio->numeros_generados;
        shuffle($numerosDesordenados);

        return view('alumno.juego', [
            'ejercicio' => $ejercicio,
            'numeros' => $numerosDesordenados,
            // Tiempo REAL restante, calculado desde fecha_inicio — no
            // el límite completo. Así, si el alumno recarga la página
            // a mitad de un ejercicio, el temporizador sigue contando
            // desde donde iba en vez de reiniciarse a full.
            'segundosRestantes' => $this->temporizador->segundosRestantes($ejercicio),
        ]);
    }

    public function comprobar(Request $request): JsonResponse
    {
        // PEA #6 / #14: límites explícitos en cada campo (coincidentes
        // con las reglas reales del juego: máximo 10 números por
        // ejercicio, cada uno dentro del rango que genera el sistema,
        // y un tiempo acotado) para no confiar ciegamente en el body.
        $datos = $request->validate([
            'ejercicio_id' => ['required', 'integer', 'exists:ejercicios,id'],
            'respuesta' => ['required', 'array', 'min:1', 'max:10'],
            'respuesta.*' => ['required', 'numeric', 'between:-1000,1000'],
        ]);

        $alumno = Auth::user();
        $ejercicio = $this->ejercicios->buscar($datos['ejercicio_id']);

        if (! $ejercicio || $ejercicio->alumno_id !== $alumno->id) {
            return response()->json(['error' => 'Ejercicio no encontrado.'], 404);
        }

        // Antes de aceptar la respuesta, se revisa si ya venció (mismo
        // respaldo síncrono que en mostrar()): así un alumno no puede
        // "ganar tiempo extra" recargando la página ni enviando la
        // respuesta justo después de que el tiempo se agotó.
        if ($this->temporizador->vencerSiCorresponde($ejercicio) || $ejercicio->estado !== 'pendiente') {
            return response()->json(['error' => 'Este ejercicio ya no está disponible (tiempo terminado o ya respondido).'], 409);
        }

        return DB::transaction(function () use ($datos, $alumno, $ejercicio) {
            $respuestaUsuario = array_map(static fn ($n) => is_numeric($n) ? $n + 0 : $n, $datos['respuesta']);

            // Se recalcula la respuesta correcta con la función recursiva
            // en vez de confiar ciegamente en el valor guardado, para que
            // la validación sea auditable e independiente del almacenamiento.
            $correcta = OrdenadorRecursivo::ordenarDescendente($ejercicio->numeros_generados);
            $esCorrecta = $respuestaUsuario === $correcta;

            // El tiempo usado se calcula en el servidor a partir de
            // fecha_inicio, nunca del contador que envía el navegador
            // (que además de manipulable, se veía afectado por el mismo
            // bug de reinicio al recargar la página).
            $tiempo = min($this->temporizador->segundosTranscurridos($ejercicio), $ejercicio->tiempo_limite_segundos);
            $puntaje = CalculadoraPuntaje::calcular($esCorrecta, $tiempo, $ejercicio->tiempo_limite_segundos, $ejercicio->dificultad);

            Respuesta::create([
                'ejercicio_id' => $ejercicio->id,
                'alumno_id' => $alumno->id,
                'respuesta_usuario' => $respuestaUsuario,
                'es_correcta' => $esCorrecta,
                'tiempo_segundos' => $tiempo,
                'puntaje_obtenido' => $puntaje,
                'respondido_por_tiempo' => false,
                'fecha_respuesta' => now(),
            ]);

            $this->ejercicios->marcarRespondido($ejercicio);
            $rendimiento = $this->aprendizaje->actualizarRendimiento($alumno);

            return response()->json([
                'correcta' => $esCorrecta,
                'respuesta_correcta' => $correcta,
                'puntaje' => $puntaje,
                'racha' => $rendimiento->racha_actual,
                'frase' => VerrityPhraseBank::una($esCorrecta ? 'rendimiento_alto' : 'rendimiento_bajo'),
            ]);
        });
    }
}
