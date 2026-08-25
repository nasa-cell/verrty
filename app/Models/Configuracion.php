<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    public $timestamps = false;

    protected $table = 'configuracion';

    protected $fillable = [
        'nombre_sistema',
        'tiempo_limite_segundos',
        'cantidad_minima_numeros',
        'cantidad_maxima_numeros',
        'puntaje_base',
        'activo',
    ];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }
}
