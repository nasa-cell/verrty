<?php

namespace Database\Seeders;

use App\Models\Logro;
use Illuminate\Database\Seeder;

class LogroSeeder extends Seeder
{
    public function run(): void
    {
        $logros = [
            ['nombre' => 'Primer ejercicio', 'descripcion' => 'Completar el primer ejercicio', 'requisito' => 1, 'icono' => 'primer-ejercicio'],
            ['nombre' => 'Cinco aciertos', 'descripcion' => 'Conseguir 5 respuestas correctas', 'requisito' => 5, 'icono' => 'cinco-aciertos'],
            ['nombre' => 'Diez aciertos', 'descripcion' => 'Conseguir 10 respuestas correctas', 'requisito' => 10, 'icono' => 'diez-aciertos'],
            ['nombre' => 'Racha de 10', 'descripcion' => 'Conseguir una racha de 10 aciertos', 'requisito' => 10, 'icono' => 'racha'],
            ['nombre' => 'Maestro del orden', 'descripcion' => 'Alcanzar 90% de precisión', 'requisito' => 90, 'icono' => 'maestro'],
        ];

        foreach ($logros as $logro) {
            Logro::firstOrCreate(['nombre' => $logro['nombre']], $logro);
        }
    }
}
