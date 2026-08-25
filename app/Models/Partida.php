<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partida extends Model
{
    public $timestamps = false;

    protected $table = 'partidas';

    protected $fillable = [
        'alumno_id',
        'total_ejercicios',
        'aciertos',
        'errores',
        'puntaje_total',
        'tiempo_total_segundos',
        'tiempo_promedio_segundos',
        'porcentaje',
        'estado',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'datetime',
            'fecha_fin' => 'datetime',
        ];
    }

    public function alumno()
    {
        return $this->belongsTo(Usuario::class, 'alumno_id');
    }
}
