<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Progreso extends Model
{
    public $timestamps = false;

    protected $table = 'progreso';

    protected $fillable = [
        'alumno_id',
        'ejercicios_completados',
        'aciertos',
        'errores',
        'porcentaje_progreso',
        'dificultad_actual',
        'puntaje_total',
        'ultima_actividad',
    ];

    protected function casts(): array
    {
        return ['ultima_actividad' => 'datetime'];
    }

    public function alumno()
    {
        return $this->belongsTo(Usuario::class, 'alumno_id');
    }
}
