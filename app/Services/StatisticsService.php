<?php

namespace App\Services;

use App\Models\RendimientoAlumno;
use App\Models\Respuesta;
use App\Models\Usuario;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * ORDENIA IA — PEA #19: consultas avanzadas y reportes estadísticos.
 * Todas las cifras que muestra Verrity y los paneles salen de aquí:
 * ninguna estadística se inventa ni se codifica de forma fija.
 */
class StatisticsService
{
    /** Estadísticas generales del sistema (COUNT, SUM, AVG, CASE). */
    public function generales(): array
    {
        $fila = DB::table('respuestas')
            ->selectRaw('COUNT(DISTINCT alumno_id) as total_alumnos')
            ->selectRaw('COUNT(*) as total_respuestas')
            ->selectRaw('COALESCE(SUM(CASE WHEN es_correcta = 1 THEN 1 ELSE 0 END), 0) as total_aciertos')
            ->selectRaw('COALESCE(SUM(CASE WHEN es_correcta = 0 THEN 1 ELSE 0 END), 0) as total_errores')
            ->selectRaw('COALESCE(AVG(tiempo_segundos), 0) as tiempo_promedio')
            ->selectRaw('COALESCE(AVG(CASE WHEN es_correcta = 1 THEN 100 ELSE 0 END), 0) as promedio_general')
            ->first();

        return (array) $fila;
    }

    /**
     * Tabla del panel del profesor: alumno | ejercicios | aciertos | errores | % | tiempo.
     *
     * Paginada (no ->get() de todo): con 400 o 4000 alumnos, cargar la
     * tabla completa en una sola petición sería lento tanto para MySQL
     * como para el navegador que tiene que dibujar la fila. Se pagina
     * en la base de datos (LIMIT/OFFSET), no en PHP.
     */
    public function rankingAlumnos(?string $busquedaAlumno = null, int $porPagina = 25): LengthAwarePaginator
    {
        $query = DB::table('usuarios as u')
            ->leftJoin('rendimiento_alumnos as r', 'r.alumno_id', '=', 'u.id')
            ->where('u.rol', 'alumno')
            ->select([
                'u.id',
                DB::raw("CONCAT(u.nombre, ' ', u.apellido) as alumno"),
                'u.email',
                DB::raw('COALESCE(r.total_ejercicios, 0) as ejercicios'),
                DB::raw('COALESCE(r.total_aciertos, 0) as aciertos'),
                DB::raw('COALESCE(r.total_errores, 0) as errores'),
                DB::raw('COALESCE(r.porcentaje_aciertos, 0) as porcentaje'),
                DB::raw('COALESCE(r.tiempo_promedio_segundos, 0) as tiempo_promedio'),
                DB::raw('COALESCE(r.dificultad_actual, 1) as dificultad'),
                DB::raw('COALESCE(r.puntaje_total, 0) as puntaje'),
                DB::raw('COALESCE(r.racha_actual, 0) as racha'),
            ]);

        if ($busquedaAlumno) {
            $query->where(function ($q) use ($busquedaAlumno) {
                $q->where('u.nombre', 'like', "%{$busquedaAlumno}%")
                    ->orWhere('u.apellido', 'like', "%{$busquedaAlumno}%")
                    ->orWhere('u.email', 'like', "%{$busquedaAlumno}%");
            });
        }

        return $query->orderByDesc('puntaje')->paginate($porPagina)->withQueryString();
    }

    /**
     * Misma consulta que rankingAlumnos(), pero SIN paginar — para usos
     * que necesitan el arreglo completo de filas en vez de una página a
     * la vez, como guardar el ranking dentro de un reporte (ReportService).
     * Se acota a $limite (por defecto 500) para que un reporte general
     * no intente serializar miles de filas si hubiera esa cantidad de
     * alumnos; en ese caso el reporte muestra a los 500 con mejor
     * puntaje en vez de la lista entera.
     *
     * @return Collection<int, object>
     */
    public function rankingAlumnosCompleto(int $limite = 500): Collection
    {
        return DB::table('usuarios as u')
            ->leftJoin('rendimiento_alumnos as r', 'r.alumno_id', '=', 'u.id')
            ->where('u.rol', 'alumno')
            ->select([
                'u.id',
                DB::raw("CONCAT(u.nombre, ' ', u.apellido) as alumno"),
                'u.email',
                DB::raw('COALESCE(r.total_ejercicios, 0) as ejercicios'),
                DB::raw('COALESCE(r.total_aciertos, 0) as aciertos'),
                DB::raw('COALESCE(r.total_errores, 0) as errores'),
                DB::raw('COALESCE(r.porcentaje_aciertos, 0) as porcentaje'),
                DB::raw('COALESCE(r.tiempo_promedio_segundos, 0) as tiempo_promedio'),
                DB::raw('COALESCE(r.dificultad_actual, 1) as dificultad'),
                DB::raw('COALESCE(r.puntaje_total, 0) as puntaje'),
                DB::raw('COALESCE(r.racha_actual, 0) as racha'),
            ])
            ->orderByDesc('puntaje')
            ->limit($limite)
            ->get();
    }

    /** Alumno con mejor rendimiento (subconsulta: MAX del porcentaje entre quienes tienen ejercicios). */
    public function mejorAlumno(): ?object
    {
        return DB::table('usuarios as u')
            ->join('rendimiento_alumnos as r', 'r.alumno_id', '=', 'u.id')
            ->where('u.rol', 'alumno')
            ->where('r.total_ejercicios', '>', 0)
            ->orderByDesc('r.porcentaje_aciertos')
            ->orderByDesc('r.puntaje_total')
            ->select('u.id', DB::raw("CONCAT(u.nombre, ' ', u.apellido) as alumno"), 'r.porcentaje_aciertos', 'r.puntaje_total')
            ->first();
    }

    /** Alumno con más errores (HAVING sobre el total de errores). */
    public function alumnoConMasErrores(): ?object
    {
        return DB::table('respuestas as resp')
            ->join('usuarios as u', 'u.id', '=', 'resp.alumno_id')
            ->select('u.id', DB::raw("CONCAT(u.nombre, ' ', u.apellido) as alumno"))
            ->selectRaw('SUM(resp.es_correcta = 0) as total_errores')
            ->groupBy('u.id', 'u.nombre', 'u.apellido')
            ->having('total_errores', '>', 0)
            ->orderByDesc('total_errores')
            ->first();
    }

    /** Alumno que más necesita practicar: menor porcentaje entre quienes ya jugaron. */
    public function alumnoQueMasNecesitaPracticar(): ?object
    {
        return DB::table('usuarios as u')
            ->join('rendimiento_alumnos as r', 'r.alumno_id', '=', 'u.id')
            ->where('u.rol', 'alumno')
            ->where('r.total_ejercicios', '>', 0)
            ->orderBy('r.porcentaje_aciertos')
            ->select('u.id', DB::raw("CONCAT(u.nombre, ' ', u.apellido) as alumno"), 'r.porcentaje_aciertos')
            ->first();
    }

    public function rendimientoDeAlumno(int $alumnoId): ?RendimientoAlumno
    {
        return RendimientoAlumno::where('alumno_id', $alumnoId)->first();
    }

    public function buscarAlumnoPorNombre(string $nombre): ?Usuario
    {
        $termino = trim($nombre);

        return Usuario::where('rol', 'alumno')
            ->where(function ($q) use ($termino) {
                $q->where('nombre', 'like', "%{$termino}%")
                    ->orWhere('apellido', 'like', "%{$termino}%")
                    ->orWhereRaw("CONCAT(nombre, ' ', apellido) LIKE ?", ["%{$termino}%"]);
            })
            ->first();
    }

    /** Ejercicios realizados por un alumno hoy (usado por Verrity: "¿cuántos ejercicios hizo hoy?"). */
    public function ejerciciosHoy(int $alumnoId): int
    {
        return Respuesta::where('alumno_id', $alumnoId)
            ->whereDate('fecha_respuesta', now()->toDateString())
            ->count();
    }

    /** Historial reciente de respuestas de un alumno (para el panel y el reporte individual). */
    public function historialAlumno(int $alumnoId, int $limite = 20): Collection
    {
        return DB::table('respuestas as resp')
            ->join('ejercicios as e', 'e.id', '=', 'resp.ejercicio_id')
            ->where('resp.alumno_id', $alumnoId)
            ->orderByDesc('resp.fecha_respuesta')
            ->limit($limite)
            ->select('resp.*', 'e.dificultad', 'e.tipo_numeros', 'e.numeros_generados', 'e.respuesta_correcta')
            ->get();
    }
}
