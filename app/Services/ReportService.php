<?php

namespace App\Services;

use App\Models\Reporte;
use App\Models\Usuario;

/**
 * ORDENIA IA — construye y persiste reportes (individuales, generales,
 * de ranking) a partir de datos reales de StatisticsService.
 */
class ReportService
{
    public function __construct(private readonly StatisticsService $estadisticas)
    {
    }

    public function reporteIndividual(Usuario $profesor, Usuario $alumno): Reporte
    {
        $rendimiento = $this->estadisticas->rendimientoDeAlumno($alumno->id);
        $historial = $this->estadisticas->historialAlumno($alumno->id, 30);

        $contenido = [
            'alumno' => $alumno->nombre_completo,
            'email' => $alumno->email,
            'rendimiento' => $rendimiento?->toArray(),
            'historial' => $historial->toArray(),
            'generado_en' => now()->toDateTimeString(),
        ];

        return Reporte::create([
            'profesor_id' => $profesor->id,
            'alumno_id' => $alumno->id,
            'tipo' => 'individual',
            'titulo' => "Rendimiento individual — {$alumno->nombre_completo}",
            'contenido' => $contenido,
            'fecha_generacion' => now(),
        ]);
    }

    public function reporteGeneral(Usuario $profesor): Reporte
    {
        $generales = $this->estadisticas->generales();
        // El reporte necesita el arreglo completo de filas (para
        // guardarlo en JSON e imprimirlo), no una página a la vez.
        $ranking = $this->estadisticas->rankingAlumnosCompleto();

        $contenido = [
            'generales' => $generales,
            'ranking' => $ranking->toArray(),
            'generado_en' => now()->toDateTimeString(),
        ];

        return Reporte::create([
            'profesor_id' => $profesor->id,
            'alumno_id' => null,
            'tipo' => 'general',
            'titulo' => 'Reporte general de rendimiento — ORDENIA IA',
            'contenido' => $contenido,
            'fecha_generacion' => now(),
        ]);
    }
}
