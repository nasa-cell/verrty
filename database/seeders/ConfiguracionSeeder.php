<?php

namespace Database\Seeders;

use App\Models\Configuracion;
use Illuminate\Database\Seeder;

class ConfiguracionSeeder extends Seeder
{
    public function run(): void
    {
        Configuracion::query()->firstOrCreate([], [
            'nombre_sistema' => 'ORDENIA IA',
            'tiempo_limite_segundos' => 30,
            'cantidad_minima_numeros' => 3,
            'cantidad_maxima_numeros' => 10,
            'puntaje_base' => 100,
            'activo' => true,
        ]);
    }
}
