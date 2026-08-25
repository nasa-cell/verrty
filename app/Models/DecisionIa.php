<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DecisionIa extends Model
{
    public $timestamps = false;

    protected $table = 'decisiones_ia';

    protected $fillable = [
        'alumno_id',
        'porcentaje_aciertos',
        'tiempo_promedio',
        'errores',
        'dificultad_anterior',
        'decision',
        'dificultad_resultante',
        'explicacion',
        'fecha_decision',
    ];

    protected function casts(): array
    {
        return ['fecha_decision' => 'datetime'];
    }

    public function alumno()
    {
        return $this->belongsTo(Usuario::class, 'alumno_id');
    }
}
