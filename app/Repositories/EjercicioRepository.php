<?php

namespace App\Repositories;

use App\Models\Ejercicio;
use App\Models\Usuario;

class EjercicioRepository
{
    public function siguientePendiente(Usuario $alumno): ?Ejercicio
    {
        return Ejercicio::where('alumno_id', $alumno->id)
            ->where('estado', 'pendiente')
            ->orderBy('fecha_generacion')
            ->first();
    }

    public function contarPendientes(Usuario $alumno): int
    {
        return Ejercicio::where('alumno_id', $alumno->id)
            ->where('estado', 'pendiente')
            ->count();
    }

    public function marcarInicio(Ejercicio $ejercicio): Ejercicio
    {
        $ejercicio->fecha_inicio = now();
        $ejercicio->save();

        return $ejercicio;
    }

    public function marcarRespondido(Ejercicio $ejercicio): Ejercicio
    {
        $ejercicio->estado = 'respondido';
        $ejercicio->fecha_fin = now();
        $ejercicio->save();

        return $ejercicio;
    }

    public function marcarVencido(Ejercicio $ejercicio): Ejercicio
    {
        $ejercicio->estado = 'vencido';
        $ejercicio->fecha_fin = now();
        $ejercicio->save();

        return $ejercicio;
    }

    public function buscar(int $id): ?Ejercicio
    {
        return Ejercicio::find($id);
    }
}
