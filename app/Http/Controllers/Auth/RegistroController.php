<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use App\Repositories\UsuarioRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * ORDENIA IA — autorregistro de ALUMNOS únicamente.
 *
 * Por diseño (ver PEA #6 Seguridad), el rol nunca se recibe del
 * formulario: se fija a "alumno" en el propio controlador. Profesores
 * y administradores solo se crean por el administrador o por seeder,
 * nunca desde un formulario público.
 */
class RegistroController extends Controller
{
    public function __construct(private readonly UsuarioRepository $usuarios)
    {
    }

    public function mostrar(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('login');
        }

        return view('auth.registro');
    }

    public function registrar(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('login');
        }

        // PEA #6 / #14: nunca se confía en la entrada del usuario. Cada
        // campo tiene un largo máximo (igual a su columna en MySQL) y
        // los nombres además se restringen a letras, espacios,
        // apóstrofes y guiones — así "O'Higgins" o "María-José" pasan,
        // pero "<script>" o cualquier intento de inyección no.
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'regex:/^[\p{L}\s\'\-]+$/u'],
            'apellido' => ['required', 'string', 'max:100', 'regex:/^[\p{L}\s\'\-]+$/u'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:usuarios,email'],
            'password' => ['required', 'string', 'min:6', 'max:72', 'confirmed'],
        ], [
            'nombre.regex' => 'El nombre solo puede contener letras, espacios, apóstrofes y guiones.',
            'apellido.regex' => 'El apellido solo puede contener letras, espacios, apóstrofes y guiones.',
        ]);

        $usuario = $this->usuarios->crear([
            'nombre' => $datos['nombre'],
            'apellido' => $datos['apellido'],
            'email' => $datos['email'],
            'password' => Hash::make($datos['password']),
            'rol' => 'alumno', // fijo: nunca viene del formulario
            'estado' => 'activo',
        ]);

        Auditoria::create([
            'usuario_id' => $usuario->id,
            'accion' => 'registro',
            'modulo' => 'auth',
            'descripcion' => 'Alumno registrado desde el formulario público',
            'ip' => $request->ip(),
            'fecha' => now(),
        ]);

        Auth::login($usuario);
        $request->session()->regenerate();
        $this->usuarios->registrarUltimoAcceso($usuario);

        return redirect()->route('alumno.dashboard')->with('mensaje', '¡Cuenta creada! Bienvenido a ORDENIA IA.');
    }
}
