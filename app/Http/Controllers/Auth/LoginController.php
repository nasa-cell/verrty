<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use App\Models\SesionUsuario;
use App\Repositories\UsuarioRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * ORDENIA IA — PEA #6: autenticación Laravel (hash de contraseña,
 * CSRF automático en el formulario, regeneración de sesión, auditoría).
 */
class LoginController extends Controller
{
    public function __construct(private readonly UsuarioRepository $usuarios)
    {
    }

    public function mostrar(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect($this->rutaSegunRol(Auth::user()->rol));
        }

        return view('auth.login');
    }

    public function iniciar(Request $request): RedirectResponse
    {
        // PEA #6 / #14: nunca se confía en la entrada del usuario. Se
        // valida formato Y longitud máxima (coincidente con las
        // columnas de la base de datos) antes de tocar Auth::attempt().
        $credenciales = $request->validate([
            'email' => ['required', 'string', 'email', 'max:150'],
            'password' => ['required', 'string', 'max:72'],
        ]);

        if (! Auth::attempt($credenciales, $request->boolean('recordar'))) {
            Auditoria::create([
                'usuario_id' => null,
                'accion' => 'login_fallido',
                'modulo' => 'auth',
                'descripcion' => "Intento fallido para {$credenciales['email']}",
                'ip' => $request->ip(),
                'fecha' => now(),
            ]);

            return back()->withErrors(['email' => 'Credenciales incorrectas.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        $usuario = Auth::user();
        $this->usuarios->registrarUltimoAcceso($usuario);

        SesionUsuario::create([
            'usuario_id' => $usuario->id,
            'token' => Str::random(64),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'inicio' => now(),
            'ultimo_acceso' => now(),
            'activa' => true,
        ]);

        Auditoria::create([
            'usuario_id' => $usuario->id,
            'accion' => 'login',
            'modulo' => 'auth',
            'descripcion' => 'Inicio de sesión correcto',
            'ip' => $request->ip(),
            'fecha' => now(),
        ]);

        return redirect()->intended($this->rutaSegunRol($usuario->rol));
    }

    public function salir(Request $request): RedirectResponse
    {
        $usuario = Auth::user();

        if ($usuario) {
            SesionUsuario::where('usuario_id', $usuario->id)->where('activa', true)
                ->update(['activa' => false, 'cierre' => now()]);

            Auditoria::create([
                'usuario_id' => $usuario->id,
                'accion' => 'logout',
                'modulo' => 'auth',
                'descripcion' => 'Cierre de sesión',
                'ip' => $request->ip(),
                'fecha' => now(),
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function rutaSegunRol(string $rol): string
    {
        return match ($rol) {
            'profesor' => route('profesor.dashboard'),
            'administrador' => route('admin.dashboard'),
            default => route('alumno.dashboard'),
        };
    }
}
