<?php

namespace App\Http\Controllers\Profesor;

use App\Http\Controllers\Controller;
use App\Repositories\UsuarioRepository;
use App\Services\StatisticsService;
use App\Services\VerrityAIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Endpoints AJAX usados por el widget de Verrity en /profesor/dashboard.
 */
class VerrityController extends Controller
{
    public function __construct(
        private readonly VerrityAIService $verrity,
        private readonly UsuarioRepository $usuarios,
        private readonly StatisticsService $estadisticas,
    ) {
    }

    public function preguntar(Request $request): JsonResponse
    {
        $datos = $request->validate(['pregunta' => ['required', 'string', 'min:2', 'max:500']]);

        $respuesta = $this->verrity->responder($datos['pregunta']);

        return response()->json($respuesta);
    }

    /**
     * Pregunta estructurada: el profesor selecciona el tipo de pregunta
     * y el alumno desde dos listas desplegables, en vez de escribir
     * texto libre. Elimina cualquier ambigüedad de interpretación para
     * las preguntas más comunes.
     */
    public function preguntarSobreAlumno(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'tipo' => ['required', 'string', Rule::in(VerrityAIService::TIPOS_PREGUNTA)],
            'alumno_id' => ['required', 'integer', 'exists:usuarios,id'],
        ]);

        $alumno = $this->usuarios->buscarPorId($datos['alumno_id']);

        if (! $alumno || ! $alumno->esAlumno()) {
            return response()->json(['error' => 'Alumno no válido.'], 422);
        }

        $respuesta = $this->verrity->responderSobreAlumno($datos['tipo'], $alumno);

        return response()->json($respuesta);
    }

    public function generar(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'alumno_id' => ['required', 'integer', 'exists:usuarios,id'],
            'cantidad' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $alumno = $this->usuarios->buscarPorId($datos['alumno_id']);

        if (! $alumno || ! $alumno->esAlumno()) {
            return response()->json(['error' => 'Alumno no válido.'], 422);
        }

        $resultado = $this->verrity->generarEjerciciosPara($alumno, Auth::user(), $datos['cantidad'] ?? 10);

        return response()->json($resultado);
    }
}
