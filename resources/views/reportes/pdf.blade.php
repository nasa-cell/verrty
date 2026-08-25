<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $reporte->titulo }}</title>
    <style>
        /* dompdf no soporta CSS grid/flexbox de forma confiable ni
           variables CSS, así que esta plantilla usa solo bloques,
           tablas y colores fijos — pensada exclusivamente para
           generar el PDF descargable, separada de la vista de
           impresión en pantalla (reportes/imprimir.blade.php). */
        body { font-family: 'Helvetica', Arial, sans-serif; color: #334155; font-size: 12px; }
        h1 { color: #1e40af; font-size: 20px; margin-bottom: 2px; }
        h2 { color: #1e40af; font-size: 15px; margin-top: 22px; margin-bottom: 8px; border-bottom: 1px solid #cbd5e1; padding-bottom: 4px; }
        .subtitulo { color: #64748b; font-size: 11px; margin-top: 0; }
        .marca { color: #2563eb; font-weight: bold; font-size: 13px; margin-bottom: 14px; }

        table.stats { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        table.stats td {
            width: 25%; background: #eff6ff; border: 1px solid #dbeafe;
            padding: 10px; text-align: center;
        }
        table.stats .valor { font-size: 18px; font-weight: bold; color: #1d4ed8; display: block; }
        table.stats .etiqueta { font-size: 9px; color: #64748b; text-transform: uppercase; }

        table.datos { width: 100%; border-collapse: collapse; font-size: 11px; }
        table.datos th {
            background: #eff6ff; color: #1e40af; text-align: left;
            padding: 6px 8px; border-bottom: 2px solid #bfdbfe;
        }
        table.datos td { padding: 6px 8px; border-bottom: 1px solid #e2e8f0; }
        table.datos tr:nth-child(even) td { background: #f8fafc; }

        .pie { margin-top: 26px; font-size: 9px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <div class="marca">🔢 ORDENIA IA</div>

    <h1>{{ $reporte->titulo }}</h1>
    <p class="subtitulo">Generado el {{ $reporte->fecha_generacion->format('d/m/Y H:i') }}</p>

    @php $c = $reporte->contenido; @endphp

    @if ($reporte->tipo === 'individual')
        <h2>Rendimiento de {{ $c['alumno'] }}</h2>

        @if ($c['rendimiento'])
            <table class="stats">
                <tr>
                    <td><span class="valor">{{ $c['rendimiento']['total_ejercicios'] }}</span><span class="etiqueta">Ejercicios</span></td>
                    <td><span class="valor">{{ $c['rendimiento']['porcentaje_aciertos'] }}%</span><span class="etiqueta">Aciertos</span></td>
                    <td><span class="valor">{{ $c['rendimiento']['tiempo_promedio_segundos'] }}s</span><span class="etiqueta">Tiempo promedio</span></td>
                    <td><span class="valor">{{ $c['rendimiento']['puntaje_total'] }}</span><span class="etiqueta">Puntaje</span></td>
                </tr>
            </table>
        @else
            <p>Sin datos de rendimiento todavía.</p>
        @endif

        <h2>Historial</h2>
        <table class="datos">
            <thead><tr><th>Fecha</th><th>Resultado</th><th>Tiempo</th><th>Puntaje</th></tr></thead>
            <tbody>
                @forelse ($c['historial'] as $h)
                    <tr>
                        <td>{{ \Illuminate\Support\Carbon::parse($h['fecha_respuesta'])->format('d/m/Y H:i') }}</td>
                        <td>{{ $h['es_correcta'] ? 'Correcto' : 'Incorrecto' }}</td>
                        <td>{{ $h['tiempo_segundos'] }}s</td>
                        <td>{{ $h['puntaje_obtenido'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">Sin historial registrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    @else
        <h2>Estadísticas generales</h2>
        <table class="stats">
            <tr>
                <td><span class="valor">{{ $c['generales']['total_alumnos'] }}</span><span class="etiqueta">Alumnos</span></td>
                <td><span class="valor">{{ $c['generales']['total_respuestas'] }}</span><span class="etiqueta">Ejercicios</span></td>
                <td><span class="valor">{{ number_format($c['generales']['promedio_general'], 1) }}%</span><span class="etiqueta">Promedio</span></td>
                <td><span class="valor">{{ number_format($c['generales']['tiempo_promedio'], 1) }}s</span><span class="etiqueta">Tiempo</span></td>
            </tr>
        </table>

        <h2>Ranking</h2>
        <table class="datos">
            <thead><tr><th>Alumno</th><th>Ejercicios</th><th>%</th><th>Puntaje</th></tr></thead>
            <tbody>
                @forelse ($c['ranking'] as $r)
                    <tr>
                        <td>{{ $r['alumno'] }}</td>
                        <td>{{ $r['ejercicios'] }}</td>
                        <td>{{ $r['porcentaje'] }}%</td>
                        <td>{{ $r['puntaje'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">Sin alumnos con actividad registrada.</td></tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <div class="pie">ORDENIA IA — Sistema educativo con IA propia (Verrity) — documento generado automáticamente</div>
</body>
</html>
