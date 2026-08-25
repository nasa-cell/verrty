<?php

namespace App\Jobs;

use App\Models\Ejercicio;
use App\Services\TimerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * ORDENIA IA — PEA #7: Timer y procesamiento en segundo plano.
 *
 * Cuando el alumno recibe un ejercicio, el controlador despacha este Job
 * con un retraso igual al tiempo límite del ejercicio (delay()). La
 * lógica real de vencimiento vive en TimerService (compartida con el
 * respaldo síncrono que corre cada vez que se abre /alumno/jugar, por
 * si no hay un worker de colas activo) — este Job solo la dispara en
 * segundo plano, sin bloquear al navegador del alumno.
 *
 * Compatibilidad con hosting gratuito: si el despliegue no permite un
 * worker persistente (ej. InfinityFree), basta con cambiar
 * QUEUE_CONNECTION a "sync" en el .env; el Job se sigue ejecutando, solo
 * que de forma inmediata en el mismo request en vez de en segundo plano.
 */
class ExpirarEjercicioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly int $ejercicioId)
    {
    }

    public function handle(TimerService $temporizador): void
    {
        $ejercicio = Ejercicio::find($this->ejercicioId);

        if (! $ejercicio) {
            return; // el ejercicio ya no existe (caso extremo, no debería pasar)
        }

        $temporizador->vencerSiCorresponde($ejercicio);
    }
}
