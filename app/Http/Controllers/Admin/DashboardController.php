<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use App\Models\ConfiguracionIa;
use App\Models\Usuario;
use App\Services\AIConfigurationManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'usuarios' => Usuario::orderBy('rol')->orderBy('nombre')->get(),
            'configuracionIa' => ConfiguracionIa::where('activo', true)->latest('id')->first(),
            'auditoria' => Auditoria::with('usuario')->latest('fecha')->limit(30)->get(),
        ]);
    }

    public function actualizarConfiguracionIa(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'porcentaje_subir' => ['required', 'numeric', 'min:0', 'max:100'],
            'porcentaje_mantener' => ['required', 'numeric', 'min:0', 'max:100'],
            'dificultad_minima' => ['required', 'integer', 'min:1', 'max:10'],
            'dificultad_maxima' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $configuracion = ConfiguracionIa::where('activo', true)->latest('id')->firstOrFail();
        $configuracion->update($datos);

        AIConfigurationManager::getInstance()->recargar();

        return back()->with('mensaje', 'Configuración de la IA actualizada.');
    }

    public function cambiarEstadoUsuario(Usuario $usuario): RedirectResponse
    {
        $usuario->estado = $usuario->estado === 'activo' ? 'inactivo' : 'activo';
        $usuario->save();

        return back()->with('mensaje', "Usuario {$usuario->nombre_completo} ahora está {$usuario->estado}.");
    }
}
