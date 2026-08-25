<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Reporte;
use App\Repositories\UsuarioRepository;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * ORDENIA IA — PEA #9: reportes e impresión. Cada reporte generado se
 * guarda en la tabla "reportes" (persistencia real, no solo pantalla).
 */
class ReporteController extends Controller
{
    public function __construct(
        private readonly ReportService $reportes,
        private readonly UsuarioRepository $usuarios,
    ) {
    }

    public function index(): View
    {
        return view('reportes.index', [
            'reportes' => Reporte::with('alumno')->latest('fecha_generacion')->paginate(20),
        ]);
    }

    public function generarGeneral(): RedirectResponse
    {
        $reporte = $this->reportes->reporteGeneral(Auth::user());

        return redirect()->route('reportes.imprimir', $reporte);
    }

    public function generarIndividual(Request $request): RedirectResponse
    {
        $datos = $request->validate(['alumno_id' => ['required', 'integer', 'exists:usuarios,id']]);
        $alumno = $this->usuarios->buscarPorId($datos['alumno_id']);

        $reporte = $this->reportes->reporteIndividual(Auth::user(), $alumno);

        return redirect()->route('reportes.imprimir', $reporte);
    }

    public function imprimir(Reporte $reporte): View
    {
        return view('reportes.imprimir', ['reporte' => $reporte]);
    }

    /**
     * Genera y entrega el reporte como un archivo PDF real (descarga
     * directa, no el diálogo de impresión del navegador).
     */
    public function descargarPdf(Reporte $reporte): Response
    {
        $pdf = Pdf::loadView('reportes.pdf', ['reporte' => $reporte])->setPaper('a4');

        $nombreArchivo = 'ordenia-ia-reporte-'.Str::slug($reporte->titulo).'-'.$reporte->id.'.pdf';

        return $pdf->download($nombreArchivo);
    }

    /**
     * Borra un reporte manualmente desde el listado (además de la
     * limpieza automática de reportes viejos, ver
     * App\Console\Commands\LimpiarDatosAntiguosCommand).
     */
    public function eliminar(Reporte $reporte): RedirectResponse
    {
        $titulo = $reporte->titulo;
        $reporte->delete();

        Auditoria::create([
            'usuario_id' => Auth::id(),
            'accion' => 'eliminar_reporte',
            'modulo' => 'reportes',
            'descripcion' => "Reporte eliminado manualmente: {$titulo}",
            'ip' => request()->ip(),
            'fecha' => now(),
        ]);

        return back()->with('mensaje', 'Reporte eliminado.');
    }
}
