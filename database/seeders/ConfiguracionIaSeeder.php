<?php

namespace Database\Seeders;

use App\Models\ConfiguracionIa;
use Illuminate\Database\Seeder;

class ConfiguracionIaSeeder extends Seeder
{
    public function run(): void
    {
        ConfiguracionIa::query()->firstOrCreate([], [
            'porcentaje_subir' => 85.00,
            'porcentaje_mantener' => 60.00,
            'porcentaje_bajar' => 59.99,
            'dificultad_minima' => 1,
            'dificultad_maxima' => 10,
            'numeros_minimos' => 3,
            'numeros_maximos' => 10,
            'activo' => true,
        ]);
    }
}
