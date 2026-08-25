<?php

namespace App\Models;

use Database\Factories\EjercicioFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ejercicio extends Model
{
    /** @use HasFactory<EjercicioFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $table = 'ejercicios';

    protected $fillable = [
        'alumno_id',
        'profesor_id',
        'numeros_generados',
        'respuesta_correcta',
        'cantidad_numeros',
        'dificultad',
        'tipo_numeros',
        'generado_por_ia',
        'estado',
        'tiempo_limite_segundos',
        'fecha_generacion',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected function casts(): array
    {
        return [
            'numeros_generados' => 'array',
            'respuesta_correcta' => 'array',
            'generado_por_ia' => 'boolean',
            'fecha_generacion' => 'datetime',
            'fecha_inicio' => 'datetime',
            'fecha_fin' => 'datetime',
        ];
    }

    public function alumno()
    {
        return $this->belongsTo(Usuario::class, 'alumno_id');
    }

    public function profesor()
    {
        return $this->belongsTo(Usuario::class, 'profesor_id');
    }

    public function respuesta()
    {
        return $this->hasOne(Respuesta::class, 'ejercicio_id');
    }
}
