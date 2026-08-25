@extends('layouts.app')

@section('content')
<div class="rejilla-stats">
    <div class="stat">
        <div class="valor">{{ $generales['total_alumnos'] ?? 0 }}</div>
        <div class="etiqueta">Alumnos activos</div>
    </div>
    <div class="stat">
        <div class="valor">{{ $generales['total_respuestas'] ?? 0 }}</div>
        <div class="etiqueta">Ejercicios realizados</div>
    </div>
    <div class="stat">
        <div class="valor">{{ number_format($generales['promedio_general'] ?? 0, 1) }}%</div>
        <div class="etiqueta">Promedio general</div>
    </div>
    <div class="stat">
        <div class="valor">{{ number_format($generales['tiempo_promedio'] ?? 0, 1) }}s</div>
        <div class="etiqueta">Tiempo promedio</div>
    </div>
</div>

<div class="layout-panel">
    <div>
        <div class="tarjeta">
            <h2 class="seccion-titulo">📊 Rendimiento de alumnos</h2>

            <form method="GET" style="display:flex;gap:8px;margin-bottom:16px;">
                <input type="text" name="alumno" placeholder="Buscar alumno..." value="{{ $busqueda }}" maxlength="100"
                       style="flex:1;padding:10px 12px;border-radius:8px;border:1px solid var(--gris-300);">
                <button type="submit" class="btn btn-secundario">Buscar</button>
            </form>

            <div class="tabla-scroll">
                <table class="tabla-ordenia">
                    <thead>
                        <tr>
                            <th>Alumno</th>
                            <th>Ejercicios</th>
                            <th>Aciertos</th>
                            <th>Errores</th>
                            <th>%</th>
                            <th>Tiempo</th>
                            <th>Racha</th>
                            <th>Puntaje</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ranking as $fila)
                            <tr>
                                <td>{{ $fila->alumno }}</td>
                                <td>{{ $fila->ejercicios }}</td>
                                <td>{{ $fila->aciertos }}</td>
                                <td>{{ $fila->errores }}</td>
                                <td>
                                    @php $p = (float) $fila->porcentaje; @endphp
                                    <span class="pill {{ $p >= 85 ? 'pill-verde' : ($p >= 60 ? 'pill-amarillo' : 'pill-rojo') }}">{{ number_format($p, 1) }}%</span>
                                </td>
                                <td>{{ number_format($fila->tiempo_promedio, 1) }}s</td>
                                <td>{{ $fila->racha }}</td>
                                <td>{{ $fila->puntaje }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8">Todavía no hay alumnos con actividad registrada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $ranking->onEachSide(1)->links('pagination.ordenia') }}
        </div>

        <div class="tarjeta" style="margin-top:16px;">
            <h2 class="seccion-titulo">🖨 Reportes</h2>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <a href="{{ route('reportes.index') }}" class="btn btn-secundario">Ver reportes generados</a>
                <form method="POST" action="{{ route('reportes.general') }}">
                    @csrf
                    <button type="submit" class="btn">Generar reporte general</button>
                </form>
            </div>
        </div>
    </div>

    <aside class="verrity-widget">
        <div class="verrity-cabecera">
            <div class="verrity-personaje">
                <div class="verrity-cuerpo">
                    <div class="verrity-ojos"><span class="ojo"></span><span class="ojo"></span></div>
                    <div class="verrity-boca"></div>
                </div>
            </div>
            <div>
                <div class="verrity-nombre">VERRITY IA</div>
                <div class="verrity-estado">Asistente del profesor</div>
            </div>
        </div>

        <div class="verrity-burbuja">¡Hola profesor! ¿Qué desea analizar?</div>

        <div class="verrity-log"></div>

        <div class="form-grupo buscador-alumno">
            <label for="buscar-alumno">1. Busca un alumno</label>
            <input type="text" id="buscar-alumno" placeholder="Escribe el nombre..." maxlength="100" autocomplete="off">
            <div id="resultados-alumno" class="buscador-alumno-resultados"></div>
            <input type="hidden" id="select-alumno">
        </div>

        <div class="form-grupo">
            <label for="select-tipo-pregunta">2. Selecciona una pregunta</label>
            <select id="select-tipo-pregunta">
                <option value="ejercicios">¿Cuántos ejercicios hizo?</option>
                <option value="ejercicios_hoy">¿Cuántos ejercicios hizo hoy?</option>
                <option value="porcentaje">¿Cuál es su porcentaje de aciertos?</option>
                <option value="errores">¿Cuántos errores tiene?</option>
                <option value="tiempo">¿Cuál es su tiempo promedio?</option>
                <option value="racha">¿Cuál es su racha actual?</option>
                <option value="puntaje">¿Cuál es su puntaje total?</option>
                <option value="dificultad">¿Qué dificultad debería tener?</option>
            </select>
        </div>

        <div class="verrity-acciones">
            <button type="button" class="btn" id="btn-preguntar-alumno" data-url="{{ route('profesor.verrity.preguntar-alumno') }}">❓ Preguntar</button>
            <button type="button" class="btn btn-exito" id="btn-generar-ejercicios" data-url="{{ route('profesor.verrity.generar') }}">🤖 Generar ejercicios</button>
        </div>

        <details style="margin:4px 0;">
            <summary style="cursor:pointer;color:var(--azul-700);font-weight:600;font-size:.85rem;">
                O escribe tu propia pregunta (ej. "¿quién tiene más errores?")
            </summary>
            <form id="form-pregunta-verrity" action="{{ route('profesor.verrity.preguntar') }}" style="margin-top:10px;">
                <div class="verrity-chat-input">
                    <input type="text" id="input-pregunta-verrity" placeholder="Escribe una pregunta..." maxlength="500">
                    <button type="submit" class="btn" style="padding:10px 16px;">➤</button>
                </div>
            </form>
        </details>

        <div class="verrity-acciones">
            <button type="button" class="btn-secundario btn" id="btn-toggle-voz">🔊 Voz activada</button>
        </div>

        <div class="form-grupo" style="margin-bottom:0;">
            <label for="select-voz">Voz de respaldo del navegador</label>
            <select id="select-voz"></select>
            <small style="color:var(--gris-500);">Verrity usa su voz descargada (Piper) por defecto; esta lista solo se usa si esa voz no está disponible en el servidor.</small>
        </div>
    </aside>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/buscador-alumno.js') }}?v={{ filemtime(public_path('assets/js/buscador-alumno.js')) }}"></script>
    <script src="{{ asset('assets/js/verrity.js') }}?v={{ filemtime(public_path('assets/js/verrity.js')) }}"></script>
    <script>
        window.crearBuscadorAlumno({
            inputId: 'buscar-alumno',
            resultadosId: 'resultados-alumno',
            hiddenId: 'select-alumno',
            url: '{{ route('profesor.alumnos.buscar') }}',
        });
    </script>
@endpush
