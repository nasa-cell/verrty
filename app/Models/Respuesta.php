<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Respuesta extends Model
{
    public $timestamps = false;

    protected $table = 'respuestas';

    protected $fillable = [
        'ejercicio_id',
        'alumno_id',
        'respuesta_usuario',
        'es_correcta',
        'tiempo_segundos',
        'puntaje_obtenido',
        'respondido_por_tiempo',
        'fecha_respuesta',
    ];

    protected function casts(): array
    {
        return [
            'respuesta_usuario' => 'array',
            'es_correcta' => 'boolean',
            'respondido_por_tiempo' => 'boolean',
            'fecha_respuesta' => 'datetime',
        ];
    }

    public function ejercicio()
    {
        return $this->belongsTo(Ejercicio::class, 'ejercicio_id');
    }

    public function alumno()
    {
        return $this->belongsTo(Usuario::class, 'alumno_id');
    }
}
