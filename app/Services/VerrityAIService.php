<?php

namespace App\Services;

use App\Models\GeneracionIa;
use App\Models\Usuario;
use App\Support\VerrityPhraseBank;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * ORDENIA IA — PEA #1: Verrity, la IA propia del sistema.
 *
 * VerrityAIService NO usa ninguna API externa de lenguaje. Interpreta
 * la pregunta del profesor con reglas y expresiones regulares propias
 * (búsqueda de intención + búsqueda del alumno mencionado por nombre
 * dentro de la tabla "usuarios") y responde siempre con datos reales
 * calculados por StatisticsService / AdaptiveLearningService.
 */
class VerrityAIService
{
    public function __construct(
        private readonly StatisticsService $estadisticas,
        private readonly AdaptiveLearningService $aprendizaje,
        private readonly ExerciseGeneratorService $generador,
    ) {
    }

    /**
     * Responde una pregunta libre del profesor.
     *
     * @return array{texto: string, datos: array|null}
     */
    public function responder(string $pregunta): array
    {
        $texto = Str::lower(Str::ascii($pregunta));

        $alumno = $this->extraerAlumnoMencionado($pregunta);

        // Preguntas que no requieren un alumno específico.
        if (Str::contains($texto, ['mas errores', 'más errores'])) {
            $fila = $this->estadisticas->alumnoConMasErrores();

            return $fila
                ? ['texto' => "El alumno con más errores es {$fila->alumno}, con {$fila->total_errores} respuestas incorrectas.", 'datos' => (array) $fila]
                : ['texto' => VerrityPhraseBank::una('sin_datos'), 'datos' => null];
        }

        if (Str::contains($texto, ['mejor rendimiento', 'mejor alumno'])) {
            $fila = $this->estadisticas->mejorAlumno();

            return $fila
                ? ['texto' => "El alumno con mejor rendimiento es {$fila->alumno}, con {$fila->porcentaje_aciertos}% de aciertos.", 'datos' => (array) $fila]
                : ['texto' => VerrityPhraseBank::una('sin_datos'), 'datos' => null];
        }

        if (Str::contains($texto, ['necesita practicar', 'debe practicar', 'practicar mas', 'practicar más'])) {
            $fila = $this->estadisticas->alumnoQueMasNecesitaPracticar();

            return $fila
                ? ['texto' => "{$fila->alumno} es quien más necesita practicar (precisión actual: {$fila->porcentaje_aciertos}%).", 'datos' => (array) $fila]
                : ['texto' => VerrityPhraseBank::una('sin_datos'), 'datos' => null];
        }

        // A partir de aquí, todas las preguntas necesitan un alumno identificado.
        if (! $alumno) {
            return ['texto' => VerrityPhraseBank::una('no_entendido'), 'datos' => null];
        }

        if (Str::contains($texto, ['cuantos ejercicios', 'cuántos ejercicios']) && Str::contains($texto, ['hoy'])) {
            return $this->responderSobreAlumno('ejercicios_hoy', $alumno);
        }

        if (Str::contains($texto, ['cuantos ejercicios', 'cuántos ejercicios'])) {
            return $this->responderSobreAlumno('ejercicios', $alumno);
        }

        if (Str::contains($texto, ['error'])) {
            return $this->responderSobreAlumno('errores', $alumno);
        }

        if (Str::contains($texto, ['racha'])) {
            return $this->responderSobreAlumno('racha', $alumno);
        }

        if (Str::contains($texto, ['puntaje', 'puntos'])) {
            return $this->responderSobreAlumno('puntaje', $alumno);
        }

        if (Str::contains($texto, ['porcentaje', 'aciertos', 'precision', 'precisión'])) {
            return $this->responderSobreAlumno('porcentaje', $alumno);
        }

        if (Str::contains($texto, ['tiempo promedio', 'tiempo'])) {
            return $this->responderSobreAlumno('tiempo', $alumno);
        }

        if (Str::contains($texto, ['dificultad'])) {
            return $this->responderSobreAlumno('dificultad', $alumno);
        }

        return ['texto' => VerrityPhraseBank::una('no_entendido'), 'datos' => null];
    }

    /**
     * Tipos de pregunta válidos para el selector estructurado del
     * panel del profesor (ver VerrityController::preguntarSobreAlumno).
     * Se exponen aquí para que el controlador pueda validarlos sin
     * duplicar la lista.
     */
    public const TIPOS_PREGUNTA = [
        'ejercicios', 'ejercicios_hoy', 'errores', 'racha', 'puntaje', 'porcentaje', 'tiempo', 'dificultad',
    ];

    /**
     * Responde una pregunta ya identificada (tipo + alumno), sin pasar
     * por el análisis de texto libre. La usa tanto responder() cuando
     * ya extrajo la intención del texto, como el selector estructurado
     * "Selecciona una pregunta + Selecciona un alumno" del panel del
     * profesor — así se elimina cualquier ambigüedad de lenguaje
     * natural para las preguntas más comunes.
     *
     * @return array{texto: string, datos: array|null}
     */
    public function responderSobreAlumno(string $tipo, Usuario $alumno): array
    {
        $rendimiento = $this->estadisticas->rendimientoDeAlumno($alumno->id);

        if ($tipo === 'ejercicios_hoy') {
            $cantidad = $this->estadisticas->ejerciciosHoy($alumno->id);

            return ['texto' => "{$alumno->nombre_completo} hizo {$cantidad} ejercicio(s) hoy.", 'datos' => ['cantidad' => $cantidad]];
        }

        if ($tipo === 'ejercicios') {
            $cantidad = $rendimiento->total_ejercicios ?? 0;

            return ['texto' => "{$alumno->nombre_completo} ha realizado {$cantidad} ejercicio(s) en total.", 'datos' => ['cantidad' => $cantidad]];
        }

        if (! $rendimiento) {
            return ['texto' => VerrityPhraseBank::una('sin_datos'), 'datos' => null];
        }

        if ($tipo === 'errores') {
            return [
                'texto' => "{$alumno->nombre_completo} tiene {$rendimiento->total_errores} error(es) registrados, sobre {$rendimiento->total_ejercicios} ejercicio(s) en total.",
                'datos' => ['errores' => $rendimiento->total_errores, 'ejercicios' => $rendimiento->total_ejercicios],
            ];
        }

        if ($tipo === 'racha') {
            return [
                'texto' => "{$alumno->nombre_completo} lleva una racha actual de {$rendimiento->racha_actual} acierto(s) seguidos (mejor racha: {$rendimiento->mejor_racha}).",
                'datos' => ['racha_actual' => $rendimiento->racha_actual, 'mejor_racha' => $rendimiento->mejor_racha],
            ];
        }

        if ($tipo === 'puntaje') {
            return [
                'texto' => "{$alumno->nombre_completo} tiene {$rendimiento->puntaje_total} punto(s) en total.",
                'datos' => ['puntaje_total' => $rendimiento->puntaje_total],
            ];
        }

        if ($tipo === 'porcentaje') {
            return [
                'texto' => "El porcentaje de aciertos de {$alumno->nombre_completo} es {$rendimiento->porcentaje_aciertos}%.",
                'datos' => ['porcentaje' => $rendimiento->porcentaje_aciertos],
            ];
        }

        if ($tipo === 'tiempo') {
            return [
                'texto' => "El tiempo promedio de {$alumno->nombre_completo} es {$rendimiento->tiempo_promedio_segundos} segundos por ejercicio.",
                'datos' => ['tiempo_promedio' => $rendimiento->tiempo_promedio_segundos],
            ];
        }

        if ($tipo === 'dificultad') {
            $resultado = $this->aprendizaje->analizarYDecidir($alumno);

            return [
                'texto' => "Analizando a {$alumno->nombre_completo}... la dificultad recomendada es {$resultado['dificultad_nueva']} (antes {$resultado['dificultad_anterior']}). {$resultado['explicacion']}",
                'datos' => $resultado,
            ];
        }

        return ['texto' => VerrityPhraseBank::una('no_entendido'), 'datos' => null];
    }

    /**
     * Busca, dentro de los alumnos registrados, si alguno de sus nombres o
     * apellidos aparece mencionado en la pregunta del profesor.
     *
     * Con pocos alumnos esto es instantáneo; con cientos o miles, cada
     * pregunta escaneaba antes el modelo COMPLETO (contraseña, fechas,
     * etc.) de cada uno. Aquí solo se traen las 3 columnas que hacen
     * falta para comparar texto, para que el costo de memoria por
     * pregunta se mantenga bajo sin importar cuántos alumnos haya.
     */
    private function extraerAlumnoMencionado(string $pregunta): ?Usuario
    {
        $texto = Str::lower(Str::ascii($pregunta));

        $candidato = Usuario::where('rol', 'alumno')
            ->select(['id', 'nombre', 'apellido'])
            ->get()
            ->first(function (Usuario $alumno) use ($texto) {
                $nombre = Str::lower(Str::ascii($alumno->nombre));
                $apellido = Str::lower(Str::ascii($alumno->apellido));

                return ($nombre !== '' && Str::contains($texto, $nombre))
                    || ($apellido !== '' && Str::contains($texto, $apellido));
            });

        // Se vuelve a buscar por id para devolver el modelo completo
        // (con la relación "rendimiento" disponible más adelante), ya
        // que arriba solo se cargaron 3 columnas a propósito.
        return $candidato ? Usuario::find($candidato->id) : null;
    }

    /**
     * Ejecuta el flujo completo de generación de ejercicios pedido por
     * el profesor: analizar -> decidir -> generar -> guardar -> explicar.
     */
    public function generarEjerciciosPara(Usuario $alumno, Usuario $profesor, int $cantidad = 10): array
    {
        return DB::transaction(function () use ($alumno, $profesor, $cantidad) {
            $rendimientoAntes = $this->estadisticas->rendimientoDeAlumno($alumno->id);
            $porcentajeAntes = $rendimientoAntes->porcentaje_aciertos ?? 0;
            $tiempoAntes = $rendimientoAntes->tiempo_promedio_segundos ?? 0;
            $dificultadAntes = $rendimientoAntes->dificultad_actual ?? 1;

            $resultado = $this->aprendizaje->analizarYDecidir($alumno);
            $ejercicios = $this->generador->generarParaAlumno($alumno, $resultado['dificultad_nueva'], $profesor, $cantidad);

            $frase = VerrityPhraseBank::una('generando');

            GeneracionIa::create([
                'profesor_id' => $profesor->id,
                'alumno_id' => $alumno->id,
                'porcentaje_anterior' => $porcentajeAntes,
                'tiempo_promedio_anterior' => $tiempoAntes,
                'dificultad_anterior' => $dificultadAntes,
                'dificultad_nueva' => $resultado['dificultad_nueva'],
                'cantidad_numeros' => count($ejercicios),
                'numeros_generados' => collect($ejercicios)->pluck('numeros_generados')->all(),
                'decision_ia' => $resultado['decision'],
                'motivo_decision' => $resultado['explicacion'],
                'frase_verrity' => $frase,
                'fecha_generacion' => now(),
            ]);

            return [
                'alumno' => $alumno,
                'cantidad' => count($ejercicios),
                'decision' => $resultado['decision'],
                'dificultad_anterior' => $dificultadAntes,
                'dificultad_nueva' => $resultado['dificultad_nueva'],
                'explicacion' => $resultado['explicacion'],
                'frase' => $frase,
                'mensaje' => "Analizando a {$alumno->nombre_completo}... 🧠 Su precisión es {$porcentajeAntes}%. {$resultado['explicacion']} ¡Listo! He generado ".count($ejercicios).' nuevos ejercicios.',
            ];
        });
    }
}
