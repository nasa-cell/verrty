@extends('layouts.app')

@section('content')
<div class="tarjeta" style="margin-top:20px;text-align:center;">
    <h1 style="color:var(--azul-800);margin-top:0;">¡Hola, {{ $alumno->nombre }}! 👋</h1>
    <p style="color:var(--gris-500);">Ordena los números de mayor a menor y sube en el ranking.</p>
    <a href="{{ route('alumno.jugar') }}" class="btn" style="font-size:1.1rem;padding:16px 34px;margin-top:10px;">🎮 JUGAR</a>
</div>

<div class="rejilla-stats">
    <div class="stat">
        <div class="valor">{{ $rendimiento->total_ejercicios ?? 0 }}</div>
        <div class="etiqueta">Ejercicios</div>
    </div>
    <div class="stat">
        <div class="valor">{{ $rendimiento->total_aciertos ?? 0 }}</div>
        <div class="etiqueta">Aciertos</div>
    </div>
    <div class="stat">
        <div class="valor">{{ number_format($rendimiento->porcentaje_aciertos ?? 0, 1) }}%</div>
        <div class="etiqueta">Porcentaje</div>
    </div>
    <div class="stat">
        <div class="valor">{{ $rendimiento->puntaje_total ?? 0 }}</div>
        <div class="etiqueta">Puntaje total</div>
    </div>
    <div class="stat">
        <div class="valor">{{ $rendimiento->racha_actual ?? 0 }} 🔥</div>
        <div class="etiqueta">Racha actual</div>
    </div>
    <div class="stat">
        <div class="valor">{{ number_format($rendimiento->tiempo_promedio_segundos ?? 0, 1) }}s</div>
        <div class="etiqueta">Tiempo promedio</div>
    </div>
</div>

<div class="tarjeta">
    <h2 class="seccion-titulo">📜 Mi historial reciente</h2>
    <div class="tabla-scroll">
        <table class="tabla-ordenia">
            <thead>
                <tr><th>Fecha</th><th>Números</th><th>Resultado</th><th>Tiempo</th><th>Puntaje</th></tr>
            </thead>
            <tbody>
                @forelse ($historial as $item)
                    <tr>
                        <td>{{ \Illuminate\Support\Carbon::parse($item->fecha_respuesta)->format('d/m/Y H:i') }}</td>
                        <td>{{ implode(' - ', json_decode($item->numeros_generados, true) ?? []) }}</td>
                        <td>
                            @if ($item->es_correcta)
                                <span class="pill pill-verde">✅ Correcto</span>
                            @else
                                <span class="pill pill-rojo">❌ Incorrecto</span>
                            @endif
                        </td>
                        <td>{{ $item->tiempo_segundos }}s</td>
                        <td>{{ $item->puntaje_obtenido }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">Todavía no has resuelto ejercicios. ¡Presiona JUGAR!</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
