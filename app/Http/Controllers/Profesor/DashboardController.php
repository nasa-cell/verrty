<?php

namespace App\Http\Controllers\Profesor;

use App\Http\Controllers\Controller;
use App\Repositories\UsuarioRepository;
use App\Services\StatisticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly StatisticsService $estadisticas,
        private readonly UsuarioRepository $usuarios,
    ) {
    }

    public function index(Request $request): View
    {
        // Aunque solo se usa en un LIKE parametrizado (sin riesgo de
        // inyección SQL), se valida igual: ningún input del usuario se
        // usa sin pasar antes por una regla explícita de longitud.
        $datos = $request->validate(['alumno' => ['nullable', 'string', 'max:100']]);
        $busqueda = $datos['alumno'] ?? null;

        return view('profesor.dashboard', [
            'generales' => $this->estadisticas->generales(),
            'ranking' => $this->estadisticas->rankingAlumnos($busqueda),
            'busqueda' => $busqueda,
        ]);
    }

    /**
     * Búsqueda en vivo de alumnos para el selector de Verrity. Devuelve
     * como máximo 15 coincidencias — pensado para funcionar igual de
     * bien con 4 alumnos que con 4000, porque nunca envía la lista
     * completa al navegador.
     */
    public function buscarAlumnos(Request $request): JsonResponse
    {
        $datos = $request->validate(['q' => ['nullable', 'string', 'max:100']]);

        $alumnos = $this->usuarios->buscarAlumnosPorTermino($datos['q'] ?? '');

        return response()->json(
            $alumnos->map(fn ($a) => ['id' => $a->id, 'nombre' => $a->nombre_completo])->values()
        );
    }
}
