<?php

namespace App\Http\Controllers;

use App\Services\VoiceService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Sirve el audio generado por Verrity (Piper TTS, voz neuronal
 * gratuita y local). Si no está disponible, responde 204 para que el
 * navegador use automáticamente su propia voz (Web Speech API).
 */
class VozController extends Controller
{
    public function __construct(private readonly VoiceService $voz)
    {
    }

    public function sintetizar(Request $request): Response|BinaryFileResponse
    {
        $datos = $request->validate(['texto' => ['required', 'string', 'max:500']]);

        $archivo = $this->voz->sintetizar($datos['texto']);

        if (! $archivo) {
            return response()->noContent(); // el frontend cae de nuevo a speechSynthesis
        }

        return response()->file($archivo, [
            'Content-Type' => 'audio/wav',
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }
}
