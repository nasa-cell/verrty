<?php

namespace App\Http\Controllers\Alumno;

use App\Http\Controllers\Controller;
use App\Services\StatisticsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly StatisticsService $estadisticas)
    {
    }

    public function index(): View
    {
        $alumno = Auth::user();

        return view('alumno.dashboard', [
            'alumno' => $alumno,
            'rendimiento' => $this->estadisticas->rendimientoDeAlumno($alumno->id),
            'historial' => $this->estadisticas->historialAlumno($alumno->id, 10),
        ]);
    }
}
