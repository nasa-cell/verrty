<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * ORDENIA IA — voz de Verrity con una voz neuronal descargada de forma
 * gratuita: Piper TTS (proyecto de código abierto, licencia MIT,
 * https://github.com/rhasspy/piper) con el modelo en español
 * "es_AR-daniela-high" (voz femenina, calidad alta, tono más vivo y
 * animado, apropiado para un personaje simpático como Verrity)
 * descargado desde Hugging Face (rhasspy/piper-voices, también de uso
 * libre). Todo corre en local, sin llamar a ninguna API externa de
 * pago ni de IA de terceros.
 *
 * Para sonar más "animada", además de elegir una voz femenina de tono
 * vivo, la síntesis se genera con --length_scale ligeramente menor a 1
 * (habla un poco más rápido/enérgico) y el navegador reproduce el
 * audio con una velocidad de reproducción levemente mayor
 * (ver public/assets/js/verrity.js), que también sube un poco el tono
 * — el mismo truco usado para voces de personajes animados — sin
 * perder inteligibilidad.
 *
 * El binario y el modelo viven en storage/piper y storage/app/voces
 * (no se suben al repositorio de Git por su peso; ver
 * storage/piper/README.md y storage/app/voces/README.md para volver a
 * descargarlos).
 *
 * COMPATIBILIDAD: Piper es un binario nativo (aquí, la build de
 * Windows). Si el hosting no permite ejecutar procesos externos —caso
 * de InfinityFree y la mayoría de hosting compartido gratuito, o un
 * servidor Linux/Mac sin este binario— la generación simplemente
 * falla de forma controlada y el navegador usa su propia voz con la
 * Web Speech API (ver public/assets/js/verrity.js), que es 100%
 * gratuita y no requiere servidor. Por eso la voz "animada" es una
 * mejora opcional, nunca una dependencia obligatoria.
 */
class VoiceService
{
    /** Fase de duración: <1.0 = habla más rápido/enérgico (más "animada"), 1.0 = velocidad natural del modelo. */
    private const LENGTH_SCALE = 0.92;

    private string $piperBinario;
    private string $modeloVoz;
    private string $carpetaCache;

    public function __construct()
    {
        $this->piperBinario = env('PIPER_BINARY', storage_path('piper/piper.exe'));
        $this->modeloVoz = env('PIPER_MODEL', storage_path('app/voces/es_AR-daniela-high.onnx'));
        $this->carpetaCache = storage_path('app/voces_generadas');

        if (! File::exists($this->carpetaCache)) {
            File::makeDirectory($this->carpetaCache, 0755, true);
        }
    }

    public function disponible(): bool
    {
        return File::exists($this->piperBinario) && File::exists($this->modeloVoz);
    }

    /**
     * Sintetiza el texto a un archivo .wav (cacheado por hash del
     * texto: la misma frase nunca se regenera dos veces) y devuelve la
     * ruta absoluta del archivo, o null si Piper no está disponible o
     * la síntesis falla.
     */
    public function sintetizar(string $texto): ?string
    {
        if (! $this->disponible()) {
            return null;
        }

        $texto = trim($texto);
        if ($texto === '') {
            return null;
        }

        // La clave de caché incluye el modelo de voz para que, si algún
        // día se cambia (ver PIPER_MODEL en .env), no se sirvan por
        // error audios viejos generados con la voz anterior.
        $claveCache = sha1($texto.'|'.basename($this->modeloVoz).'|'.self::LENGTH_SCALE);
        $archivo = $this->carpetaCache.DIRECTORY_SEPARATOR.$claveCache.'.wav';

        if (File::exists($archivo)) {
            return $archivo;
        }

        try {
            $proceso = new Process([
                $this->piperBinario,
                '--model', $this->modeloVoz,
                '--length_scale', (string) self::LENGTH_SCALE,
                '--output_file', $archivo,
            ]);
            $proceso->setInput($texto);
            $proceso->setTimeout(20);
            $proceso->run();

            if (! $proceso->isSuccessful()) {
                throw new ProcessFailedException($proceso);
            }

            return File::exists($archivo) ? $archivo : null;
        } catch (\Throwable $e) {
            Log::warning('VoiceService: no se pudo generar audio con Piper, se usará la voz del navegador.', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
