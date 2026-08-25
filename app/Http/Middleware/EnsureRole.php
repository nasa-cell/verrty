<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * ORDENIA IA — PEA #6 / #14: control de acceso por rol.
 * El alumno JAMÁS puede acceder a /profesor/* ni /admin/*, y viceversa.
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = Auth::user();

        if (! $usuario || ! in_array($usuario->rol, $roles, true)) {
            abort(403, 'No tiene permisos para acceder a esta sección.');
        }

        if ($usuario->estado !== 'activo') {
            Auth::logout();
            abort(403, 'Su cuenta está inactiva.');
        }

        return $next($request);
    }
}
