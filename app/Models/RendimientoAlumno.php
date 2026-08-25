<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RendimientoAlumno extends Model
{
    protected $table = 'rendimiento_alumnos';

    protected $fillable = [
        'alumno_id',
        'total_ejercicios',
        'total_aciertos',
        'total_errores',
        'porcentaje_aciertos',
        'porcentaje_errores',
        'tiempo_promedio_segundos',
        'dificultad_actual',
        'racha_actual',
        'mejor_racha',
        'puntaje_total',
        'ultima_evaluacion',
    ];

    protected function casts(): array
    {
        return [
            'porcentaje_aciertos' => 'decimal:2',
            'porcentaje_errores' => 'decimal:2',
            'tiempo_promedio_segundos' => 'decimal:2',
            'ultima_evaluacion' => 'datetime',
        ];
    }

    public function alumno()
    {
        return $this->belongsTo(Usuario::class, 'alumno_id');
    }
}
