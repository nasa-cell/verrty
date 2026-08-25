@extends('layouts.app')

@section('content')
<div class="tarjeta" style="margin-top:20px;">
    <h2 class="seccion-titulo">📄 Generar reporte individual</h2>
    <form method="POST" action="{{ route('reportes.individual') }}" style="display:flex;gap:10px;align-items:flex-start;">
        @csrf
        <div class="buscador-alumno" style="flex:1;">
            <input type="text" id="buscar-alumno-reporte" placeholder="Escribe el nombre del alumno..." maxlength="100" autocomplete="off"
                   style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid var(--gris-300);">
            <div id="resultados-alumno-reporte" class="buscador-alumno-resultados"></div>
            <input type="hidden" name="alumno_id" id="alumno-id-reporte" required>
        </div>
        <button type="submit" class="btn">Generar</button>
    </form>
</div>

<div class="tarjeta" style="margin-top:16px;">
    <h2 class="seccion-titulo">🗂️ Reportes generados</h2>
    <div class="tabla-scroll">
        <table class="tabla-ordenia">
            <thead><tr><th>Fecha</th><th>Tipo</th><th>Título</th><th></th></tr></thead>
            <tbody>
                @forelse ($reportes as $reporte)
                    <tr>
                        <td>{{ $reporte->fecha_generacion->format('d/m/Y H:i') }}</td>
                        <td>{{ ucfirst($reporte->tipo) }}</td>
                        <td>{{ $reporte->titulo }}</td>
                        <td style="display:flex;gap:6px;flex-wrap:wrap;">
                            <a href="{{ route('reportes.imprimir', $reporte) }}" class="btn btn-secundario" style="padding:6px 12px;font-size:.8rem;">Ver</a>
                            <a href="{{ route('reportes.pdf', $reporte) }}" class="btn btn-exito" style="padding:6px 12px;font-size:.8rem;">⬇ PDF</a>
                            <form method="POST" action="{{ route('reportes.eliminar', $reporte) }}"
                                  data-confirmar="¿Eliminar este reporte? Esta acción no se puede deshacer.">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-peligro" style="padding:6px 12px;font-size:.8rem;">🗑 Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4">Aún no se han generado reportes.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $reportes->onEachSide(1)->links('pagination.ordenia') }}
</div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/buscador-alumno.js') }}?v={{ filemtime(public_path('assets/js/buscador-alumno.js')) }}"></script>
    <script>
        window.crearBuscadorAlumno({
            inputId: 'buscar-alumno-reporte',
            resultadosId: 'resultados-alumno-reporte',
            hiddenId: 'alumno-id-reporte',
            url: '{{ route('profesor.alumnos.buscar') }}',
        });
    </script>
@endpush
