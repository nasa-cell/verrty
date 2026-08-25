<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionIa extends Model
{
    protected $table = 'configuracion_ia';

    protected $fillable = [
        'porcentaje_subir',
        'porcentaje_mantener',
        'porcentaje_bajar',
        'dificultad_minima',
        'dificultad_maxima',
        'numeros_minimos',
        'numeros_maximos',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'porcentaje_subir' => 'decimal:2',
            'porcentaje_mantener' => 'decimal:2',
            'porcentaje_bajar' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }
}
