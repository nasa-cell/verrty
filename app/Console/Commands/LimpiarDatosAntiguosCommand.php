<?php

namespace App\Console\Commands;

use App\Models\Auditoria;
use App\Models\Reporte;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * ORDENIA IA — mantenimiento: desecha datos viejos que ya no aportan
 * valor, para que el sistema siga rindiendo bien aunque haya cientos
 * o miles de alumnos generando actividad todos los días:
 *
 * - reportes: cada uno guarda un JSON con el historial completo del
 *   alumno o el ranking general; con muchos profesores generando
 *   reportes a diario, la tabla crece sin límite si nadie los borra.
 * - audios de Verrity cacheados (storage/app/voces_generadas): se
 *   regeneran solos la próxima vez que se necesiten, así que no hay
 *   ningún costo en borrar los viejos.
 * - auditoría: se guarda más tiempo (es un registro de seguridad),
 *   pero tampoco para siempre.
 *
 * Uso manual: php artisan ordenia:limpiar
 * Ya queda programado para correr solo todos los días (routes/console.php).
 */
class LimpiarDatosAntiguosCommand extends Command
{
    protected $signature = 'ordenia:limpiar
                            {--reportes-dias=30 : Días de antigüedad para borrar reportes generados}
                            {--voces-dias=14 : Días de antigüedad para borrar audios de Verrity cacheados}
                            {--auditoria-dias=180 : Días de antigüedad para borrar registros de auditoría}
                            {--dry-run : Solo mostrar qué se borraría, sin borrar nada}';

    protected $description = 'Borra reportes, audios cacheados de Verrity y auditoría antiguos para mantener el sistema liviano.';

    public function handle(): int
    {
        $simulacion = (bool) $this->option('dry-run');

        $this->limpiarReportes((int) $this->option('reportes-dias'), $simulacion);
        $this->limpiarVocesCacheadas((int) $this->option('voces-dias'), $simulacion);
        $this->limpiarAuditoria((int) $this->option('auditoria-dias'), $simulacion);

        return self::SUCCESS;
    }

    private function limpiarReportes(int $dias, bool $simulacion): void
    {
        $consulta = Reporte::where('fecha_generacion', '<', now()->subDays($dias));
        $total = $consulta->count();

        if ($simulacion) {
            $this->info("[dry-run] Se borrarían {$total} reporte(s) de más de {$dias} día(s).");

            return;
        }

        $consulta->delete();
        $this->info("Reportes borrados (más de {$dias} día(s)): {$total}");
    }

    private function limpiarVocesCacheadas(int $dias, bool $simulacion): void
    {
        $carpeta = storage_path('app/voces_generadas');

        if (! File::isDirectory($carpeta)) {
            $this->info('No hay carpeta de audios cacheados que limpiar.');

            return;
        }

        $limite = now()->subDays($dias)->timestamp;
        $borrados = 0;

        foreach (File::files($carpeta) as $archivo) {
            if ($archivo->getMTime() >= $limite) {
                continue;
            }

            if (! $simulacion) {
                File::delete($archivo->getPathname());
            }

            $borrados++;
        }

        $accion = $simulacion ? 'Se borrarían' : 'Borrados';
        $this->info("{$accion} {$borrados} audio(s) cacheado(s) de más de {$dias} día(s).");
    }

    private function limpiarAuditoria(int $dias, bool $simulacion): void
    {
        $consulta = Auditoria::where('fecha', '<', now()->subDays($dias));
        $total = $consulta->count();

        if ($simulacion) {
            $this->info("[dry-run] Se borrarían {$total} registro(s) de auditoría de más de {$dias} día(s).");

            return;
        }

        $consulta->delete();
        $this->info("Registros de auditoría borrados (más de {$dias} día(s)): {$total}");
    }
}
