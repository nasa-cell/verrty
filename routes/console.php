<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Mantenimiento diario: borra reportes viejos, audios de Verrity
// cacheados y auditoría antigua (ver LimpiarDatosAntiguosCommand).
// Para que esto corra solo, el servidor necesita un cron real
// apuntando a "php artisan schedule:run" cada minuto — en Windows con
// el Programador de tareas, en Linux con crontab. Sin ese cron
// externo, se puede seguir corriendo a mano: php artisan ordenia:limpiar
Schedule::command('ordenia:limpiar')->daily();
