@extends('layouts.app')

@section('content')
<div style="max-width:420px;margin:48px auto;">
    <div class="tarjeta">
        <div style="text-align:center;margin-bottom:20px;">
            <div style="font-size:2.4rem;">🔢🔵</div>
            <h1 style="color:var(--azul-800);margin:6px 0 0;">ORDENIA IA</h1>
            <p style="color:var(--gris-500);margin:4px 0 0;">Ordena de mayor a menor. Verrity se encarga del resto.</p>
        </div>

        <form method="POST" action="{{ route('login.intentar') }}">
            @csrf

            <div class="form-grupo">
                <label for="email">Correo electrónico</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" maxlength="150" required autofocus>
                @error('email')
                    <div class="error-form">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-grupo">
                <label for="password">Contraseña</label>
                <input type="password" name="password" id="password" maxlength="72" required>
            </div>

            <div class="form-grupo" style="display:flex;align-items:center;gap:8px;">
                <input type="checkbox" name="recordar" id="recordar" style="width:auto;">
                <label for="recordar" style="margin:0;">Recordarme</label>
            </div>

            <button type="submit" class="btn btn-bloque">Iniciar sesión</button>
        </form>

        <a href="{{ route('registro') }}" class="btn btn-secundario btn-bloque" style="margin-top:10px;">
            🎓 Registrarme como alumno
        </a>

        <div style="margin-top:18px;font-size:.78rem;color:var(--gris-500);text-align:center;">
            Usuarios de prueba: profesor@ordenia.com / alumno@ordenia.com / admin@ordenia.com
        </div>
    </div>
</div>
@endsection
