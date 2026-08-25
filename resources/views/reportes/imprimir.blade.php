@extends('layouts.app')

@section('content')
@php $c = $reporte->contenido; @endphp

<div class="tarjeta" style="margin-top:20px;">
    <div class="no-imprimir" style="margin-bottom:16px;display:flex;gap:10px;flex-wrap:wrap;">
        <a href="{{ route('reportes.pdf', $reporte) }}" class="btn btn-exito">⬇ Descargar PDF</a>
        <button onclick="window.print()" class="btn btn-secundario">🖨 Imprimir</button>
        <a href="{{ route('reportes.index') }}" class="btn btn-secundario">← Volver a reportes</a>
    </div>

    <h1 style="color:var(--azul-800);">{{ $reporte->titulo }}</h1>
    <p style="color:var(--gris-500);">Generado el {{ $reporte->fecha_generacion->format('d/m/Y H:i') }}</p>

    @if ($reporte->tipo === 'individual')
        <h2 class="seccion-titulo">Rendimiento de {{ $c['alumno'] }}</h2>
        @if ($c['rendimiento'])
            <div class="rejilla-stats" style="grid-template-columns:repeat(4,1fr);">
                <div class="stat"><div class="valor">{{ $c['rendimiento']['total_ejercicios'] }}</div><div class="etiqueta">Ejercicios</div></div>
                <div class="stat"><div class="valor">{{ $c['rendimiento']['porcentaje_aciertos'] }}%</div><div class="etiqueta">Aciertos</div></div>
                <div class="stat"><div class="valor">{{ $c['rendimiento']['tiempo_promedio_segundos'] }}s</div><div class="etiqueta">Tiempo promedio</div></div>
                <div class="stat"><div class="valor">{{ $c['rendimiento']['puntaje_total'] }}</div><div class="etiqueta">Puntaje</div></div>
            </div>
        @else
            <p>Sin datos de rendimiento todavía.</p>
        @endif

        <h2 class="seccion-titulo">Historial</h2>
        <div class="tabla-scroll">
            <table class="tabla-ordenia">
                <thead><tr><th>Fecha</th><th>Resultado</th><th>Tiempo</th><th>Puntaje</th></tr></thead>
                <tbody>
                    @foreach ($c['historial'] as $h)
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($h['fecha_respuesta'])->format('d/m/Y H:i') }}</td>
                            <td>{{ $h['es_correcta'] ? '✅ Correcto' : '❌ Incorrecto' }}</td>
                            <td>{{ $h['tiempo_segundos'] }}s</td>
                            <td>{{ $h['puntaje_obtenido'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <h2 class="seccion-titulo">Estadísticas generales</h2>
        <div class="rejilla-stats" style="grid-template-columns:repeat(4,1fr);">
            <div class="stat"><div class="valor">{{ $c['generales']['total_alumnos'] }}</div><div class="etiqueta">Alumnos</div></div>
            <div class="stat"><div class="valor">{{ $c['generales']['total_respuestas'] }}</div><div class="etiqueta">Ejercicios</div></div>
            <div class="stat"><div class="valor">{{ number_format($c['generales']['promedio_general'],1) }}%</div><div class="etiqueta">Promedio</div></div>
            <div class="stat"><div class="valor">{{ number_format($c['generales']['tiempo_promedio'],1) }}s</div><div class="etiqueta">Tiempo</div></div>
        </div>

        <h2 class="seccion-titulo">Ranking</h2>
        <div class="tabla-scroll">
            <table class="tabla-ordenia">
                <thead><tr><th>Alumno</th><th>Ejercicios</th><th>%</th><th>Puntaje</th></tr></thead>
                <tbody>
                    @foreach ($c['ranking'] as $r)
                        <tr>
                            <td>{{ $r['alumno'] }}</td>
                            <td>{{ $r['ejercicios'] }}</td>
                            <td>{{ $r['porcentaje'] }}%</td>
                            <td>{{ $r['puntaje'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
