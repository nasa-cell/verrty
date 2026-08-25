@extends('layouts.app')

@section('content')
<div style="max-width:440px;margin:48px auto;">
    <div class="tarjeta">
        <div style="text-align:center;margin-bottom:20px;">
            <div style="font-size:2.4rem;">🔢🔵</div>
            <h1 style="color:var(--azul-800);margin:6px 0 0;">Crear cuenta de alumno</h1>
            <p style="color:var(--gris-500);margin:4px 0 0;">Regístrate para empezar a jugar y practicar.</p>
        </div>

        <form method="POST" action="{{ route('registro.crear') }}">
            @csrf

            <div class="form-grupo">
                <label for="nombre">Nombre</label>
                <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" maxlength="100" pattern="[\p{L}\s'\-]+" required autofocus>
                @error('nombre')
                    <div class="error-form">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-grupo">
                <label for="apellido">Apellido</label>
                <input type="text" name="apellido" id="apellido" value="{{ old('apellido') }}" maxlength="100" pattern="[\p{L}\s'\-]+" required>
                @error('apellido')
                    <div class="error-form">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-grupo">
                <label for="email">Correo electrónico</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" maxlength="150" required>
                @error('email')
                    <div class="error-form">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-grupo">
                <label for="password">Contraseña</label>
                <input type="password" name="password" id="password" minlength="6" maxlength="72" required>
                @error('password')
                    <div class="error-form">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-grupo">
                <label for="password_confirmation">Confirmar contraseña</label>
                <input type="password" name="password_confirmation" id="password_confirmation" minlength="6" maxlength="72" required>
            </div>

            <button type="submit" class="btn btn-bloque">Crear mi cuenta</button>
        </form>

        <div style="margin-top:18px;font-size:.85rem;color:var(--gris-500);text-align:center;">
            ¿Ya tienes cuenta? <a href="{{ route('login') }}" style="color:var(--azul-700);font-weight:600;">Inicia sesión</a>
        </div>
    </div>
</div>
@endsection
