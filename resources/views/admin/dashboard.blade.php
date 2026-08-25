@extends('layouts.app')

@section('content')
<div class="tarjeta" style="margin-top:20px;">
    <h2 class="seccion-titulo">⚙️ Parámetros de la IA de Verrity</h2>
    <form method="POST" action="{{ route('admin.configuracion-ia.actualizar') }}">
        @csrf
        @method('PATCH')
        <div class="rejilla-stats" style="grid-template-columns:repeat(4,1fr);">
            <div class="form-grupo">
                <label>% para subir dificultad</label>
                <input type="number" step="0.01" min="0" max="100" name="porcentaje_subir" value="{{ $configuracionIa->porcentaje_subir ?? 85 }}">
            </div>
            <div class="form-grupo">
                <label>% para mantener dificultad</label>
                <input type="number" step="0.01" min="0" max="100" name="porcentaje_mantener" value="{{ $configuracionIa->porcentaje_mantener ?? 60 }}">
            </div>
            <div class="form-grupo">
                <label>Dificultad mínima</label>
                <input type="number" min="1" max="10" name="dificultad_minima" value="{{ $configuracionIa->dificultad_minima ?? 1 }}">
            </div>
            <div class="form-grupo">
                <label>Dificultad máxima</label>
                <input type="number" min="1" max="10" name="dificultad_maxima" value="{{ $configuracionIa->dificultad_maxima ?? 10 }}">
            </div>
        </div>
        <button type="submit" class="btn">Guardar configuración</button>
    </form>
</div>

<div class="tarjeta" style="margin-top:16px;">
    <h2 class="seccion-titulo">👥 Usuarios</h2>
    <div class="tabla-scroll">
        <table class="tabla-ordenia">
            <thead><tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Estado</th><th></th></tr></thead>
            <tbody>
                @foreach ($usuarios as $usuario)
                    <tr>
                        <td>{{ $usuario->nombre_completo }}</td>
                        <td>{{ $usuario->email }}</td>
                        <td>{{ ucfirst($usuario->rol) }}</td>
                        <td>
                            <span class="pill {{ $usuario->estado === 'activo' ? 'pill-verde' : 'pill-rojo' }}">{{ $usuario->estado }}</span>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.usuarios.estado', $usuario) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-secundario" style="padding:6px 12px;font-size:.8rem;">
                                    {{ $usuario->estado === 'activo' ? 'Desactivar' : 'Activar' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="tarjeta" style="margin-top:16px;">
    <h2 class="seccion-titulo">🛡️ Auditoría reciente</h2>
    <div class="tabla-scroll">
        <table class="tabla-ordenia">
            <thead><tr><th>Fecha</th><th>Usuario</th><th>Acción</th><th>Módulo</th><th>IP</th></tr></thead>
            <tbody>
                @forelse ($auditoria as $registro)
                    <tr>
                        <td>{{ $registro->fecha->format('d/m/Y H:i') }}</td>
                        <td>{{ $registro->usuario->nombre_completo ?? '—' }}</td>
                        <td>{{ $registro->accion }}</td>
                        <td>{{ $registro->modulo }}</td>
                        <td>{{ $registro->ip }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">Sin registros de auditoría.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
