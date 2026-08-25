<?php

namespace App\Services;

use App\Models\ConfiguracionIa;

/**
 * ORDENIA IA — PEA #4: Singleton real.
 *
 * AIConfigurationManager mantiene UNA sola instancia en memoria con los
 * parámetros activos del árbol de decisiones de Verrity (umbrales de
 * porcentaje, rango de dificultad, cantidad de números). Se usa el
 * patrón Singleton clásico (constructor privado + getInstance()) y no
 * el contenedor de Laravel, para que el patrón sea explícito y
 * verificable como estructura de POO real.
 *
 * ¿Por qué Singleton y no una simple consulta repetida?
 * - DecisionTreeService, ExerciseGeneratorService y VerrityAIService
 *   necesitan leer los mismos parámetros de IA muchas veces durante una
 *   sola petición HTTP (por ejemplo, al generar 10 ejercicios seguidos).
 * - Sin Singleton, cada llamada repetiría una consulta SELECT idéntica.
 * - Con Singleton, la fila activa de "configuracion_ia" se carga una
 *   sola vez por ciclo de vida de la aplicación (o hasta que se llama
 *   explícitamente a recargar()), garantizando también que todos los
 *   servicios usen exactamente los mismos umbrales durante la misma
 *   operación (consistencia).
 */
class AIConfigurationManager
{
    private static ?AIConfigurationManager $instancia = null;

    private ConfiguracionIa $configuracion;

    /** El constructor es privado: nadie puede hacer "new AIConfigurationManager()". */
    private function __construct()
    {
        $this->cargarConfiguracion();
    }

    public static function getInstance(): self
    {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }

        return self::$instancia;
    }

    /** Fuerza una nueva lectura desde la base de datos (por ejemplo tras editarla el administrador). */
    public function recargar(): void
    {
        $this->cargarConfiguracion();
    }

    private function cargarConfiguracion(): void
    {
        $this->configuracion = ConfiguracionIa::where('activo', true)->latest('id')->first()
            ?? new ConfiguracionIa([
                'porcentaje_subir' => 85.00,
                'porcentaje_mantener' => 60.00,
                'porcentaje_bajar' => 59.99,
                'dificultad_minima' => 1,
                'dificultad_maxima' => 10,
                'numeros_minimos' => 3,
                'numeros_maximos' => 10,
                'activo' => true,
            ]);
    }

    public function porcentajeSubir(): float
    {
        return (float) $this->configuracion->porcentaje_subir;
    }

    public function porcentajeMantener(): float
    {
        return (float) $this->configuracion->porcentaje_mantener;
    }

    public function dificultadMinima(): int
    {
        return (int) $this->configuracion->dificultad_minima;
    }

    public function dificultadMaxima(): int
    {
        return (int) $this->configuracion->dificultad_maxima;
    }

    public function numerosMinimos(): int
    {
        return (int) $this->configuracion->numeros_minimos;
    }

    public function numerosMaximos(): int
    {
        return (int) $this->configuracion->numeros_maximos;
    }

    public function modelo(): ConfiguracionIa
    {
        return $this->configuracion;
    }
}
