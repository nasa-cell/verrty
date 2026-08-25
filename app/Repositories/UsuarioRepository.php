<?php

namespace App\Repositories;

use App\Models\Usuario;
use Illuminate\Support\Collection;

/**
 * ORDENIA IA — PEA #4: arquitectura N-capas.
 * Controller -> Service -> Repository -> Model -> MySQL.
 * Esta clase aísla el acceso a la tabla "usuarios" para que los
 * controladores y servicios nunca construyan consultas Eloquent
 * directamente sobre el modelo.
 */
class UsuarioRepository
{
    public function buscarPorEmail(string $email): ?Usuario
    {
        return Usuario::where('email', $email)->first();
    }

    public function buscarPorId(int $id): ?Usuario
    {
        return Usuario::find($id);
    }

    public function alumnos(): Collection
    {
        return Usuario::where('rol', 'alumno')->orderBy('nombre')->get();
    }

    /**
     * Búsqueda acotada de alumnos por nombre/apellido/email, pensada
     * para escalar a cientos o miles de alumnos: nunca trae la tabla
     * completa, solo hasta $limite coincidencias y solo las columnas
     * que realmente se muestran (no contraseñas ni timestamps).
     *
     * @return Collection<int, Usuario>
     */
    public function buscarAlumnosPorTermino(string $termino, int $limite = 15): Collection
    {
        $termino = trim($termino);

        $consulta = Usuario::where('rol', 'alumno')
            ->select(['id', 'nombre', 'apellido', 'email']);

        if ($termino !== '') {
            $consulta->where(function ($q) use ($termino) {
                $q->where('nombre', 'like', "%{$termino}%")
                    ->orWhere('apellido', 'like', "%{$termino}%")
                    ->orWhere('email', 'like', "%{$termino}%");
            });
        }

        return $consulta->orderBy('nombre')->limit($limite)->get();
    }

    public function profesores(): Collection
    {
        return Usuario::where('rol', 'profesor')->orderBy('nombre')->get();
    }

    public function registrarUltimoAcceso(Usuario $usuario): void
    {
        $usuario->forceFill(['ultimo_acceso' => now()])->save();
    }

    public function crear(array $datos): Usuario
    {
        return Usuario::create($datos);
    }

    public function actualizar(Usuario $usuario, array $datos): Usuario
    {
        $usuario->fill($datos);
        $usuario->save();

        return $usuario;
    }

    public function eliminar(Usuario $usuario): void
    {
        $usuario->delete();
    }
}
