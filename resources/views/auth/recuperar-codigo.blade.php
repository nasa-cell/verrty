@extends('layouts.app')

@section('content')
<div style="max-width:420px;margin:48px auto;">
    <div class="tarjeta">
        <div style="text-align:center;margin-bottom:20px;">
            <div style="font-size:2.4rem;">🔑</div>
            <h1 style="color:var(--azul-800);margin:6px 0 0;">Recuperar contraseña</h1>
            <p style="color:var(--gris-500);margin:4px 0 0;">Solo para profesores. Pedile el código al administrador del sistema.</p>
        </div>

        <form method="POST" action="{{ route('recuperar.profesor.verificar') }}">
            @csrf

            <div class="form-grupo">
                <label for="email">Correo electrónico</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" maxlength="150" required autofocus>
            </div>

            <div class="form-grupo">
                <label for="codigo">Código de recuperación</label>
                <input type="text" name="codigo" id="codigo" maxlength="20" required>
                @error('codigo')
                    <div class="error-form">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-bloque">Verificar código</button>
        </form>

        <a href="{{ route('login') }}" class="btn btn-secundario btn-bloque" style="margin-top:10px;">
            ← Volver al inicio de sesión
        </a>
    </div>
</div>
@endsection
