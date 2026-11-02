<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use App\Repositories\UsuarioRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * ORDENIA IA — recuperación de contraseña para PROFESORES.
 *
 * Sin envío de correo real (MAIL_MAILER=log): en su lugar, el profesor
 * confirma su identidad con un código fijo del sistema (ver
 * config/recuperacion.php). Si el código es correcto, se lo deja pasar
 * a elegir una contraseña nueva directamente; el código en sí no
 * recupera ni cambia nada por su cuenta.
 */
class RecuperacionController extends Controller
{
    /** Minutos que dura la ventana para elegir la contraseña nueva tras verificar el código. */
    private const MINUTOS_VENTANA = 10;

    public function __construct(private readonly UsuarioRepository $usuarios)
    {
    }

    public function mostrarCodigo(): View
    {
        return view('auth.recuperar-codigo');
    }

    public function verificarCodigo(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'email' => ['required', 'string', 'email', 'max:150'],
            'codigo' => ['required', 'string', 'max:20'],
        ]);

        $usuario = $this->usuarios->buscarPorEmail($datos['email']);
        $codigoValido = hash_equals(
            (string) config('recuperacion.codigo_profesor'),
            $datos['codigo']
        );

        // Mensaje genérico a propósito: no decimos si falló el email o
        // el código, para no revelar qué correos existen en el sistema.
        if (! $usuario || ! $usuario->esProfesor() || ! $codigoValido) {
            Auditoria::create([
                'usuario_id' => $usuario?->id,
                'accion' => 'recuperacion_fallida',
                'modulo' => 'auth',
                'descripcion' => "Código de recuperación incorrecto para {$datos['email']}",
                'ip' => $request->ip(),
                'fecha' => now(),
            ]);

            return back()->withErrors(['codigo' => 'Correo o código incorrecto.'])->onlyInput('email');
        }

        $request->session()->put('recuperacion.email', $usuario->email);
        $request->session()->put('recuperacion.expira', now()->addMinutes(self::MINUTOS_VENTANA));

        return redirect()->route('recuperar.profesor.nueva');
    }

    public function mostrarNuevaContrasena(Request $request): View|RedirectResponse
    {
        if (! $this->ventanaVigente($request)) {
            return redirect()->route('recuperar.profesor.mostrar')
                ->withErrors(['codigo' => 'Verificá el código de nuevo para continuar.']);
        }

        return view('auth.recuperar-nueva-contrasena');
    }

    public function guardarNuevaContrasena(Request $request): RedirectResponse
    {
        if (! $this->ventanaVigente($request)) {
            return redirect()->route('recuperar.profesor.mostrar')
                ->withErrors(['codigo' => 'Verificá el código de nuevo para continuar.']);
        }

        $datos = $request->validate([
            'password' => ['required', 'string', 'min:6', 'max:72', 'confirmed'],
        ]);

        $usuario = $this->usuarios->buscarPorEmail($request->session()->get('recuperacion.email'));

        if (! $usuario) {
            return redirect()->route('recuperar.profesor.mostrar')
                ->withErrors(['codigo' => 'No se encontró la cuenta. Empezá de nuevo.']);
        }

        $this->usuarios->actualizar($usuario, ['password' => Hash::make($datos['password'])]);

        $request->session()->forget(['recuperacion.email', 'recuperacion.expira']);

        Auditoria::create([
            'usuario_id' => $usuario->id,
            'accion' => 'recuperacion_completada',
            'modulo' => 'auth',
            'descripcion' => 'Contraseña restablecida por código',
            'ip' => $request->ip(),
            'fecha' => now(),
        ]);

        return redirect()->route('login')->with('mensaje', 'Contraseña actualizada. Ya podés iniciar sesión.');
    }

    private function ventanaVigente(Request $request): bool
    {
        $email = $request->session()->get('recuperacion.email');
        $expira = $request->session()->get('recuperacion.expira');

        return $email && $expira && now()->lt($expira);
    }
}
