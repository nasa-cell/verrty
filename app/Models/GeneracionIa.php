<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneracionIa extends Model
{
    public $timestamps = false;

    protected $table = 'generaciones_ia';

    protected $fillable = [
        'profesor_id',
        'alumno_id',
        'porcentaje_anterior',
        'tiempo_promedio_anterior',
        'dificultad_anterior',
        'dificultad_nueva',
        'cantidad_numeros',
        'numeros_generados',
        'decision_ia',
        'motivo_decision',
        'frase_verrity',
        'fecha_generacion',
    ];

    protected function casts(): array
    {
        return [
            'numeros_generados' => 'array',
            'fecha_generacion' => 'datetime',
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
}
