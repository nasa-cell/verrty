@extends('layouts.app')

@section('content')
<div style="max-width:420px;margin:48px auto;">
    <div class="tarjeta">
        <div style="text-align:center;margin-bottom:20px;">
            <div style="font-size:2.4rem;">🔒</div>
            <h1 style="color:var(--azul-800);margin:6px 0 0;">Elegí una contraseña nueva</h1>
            <p style="color:var(--gris-500);margin:4px 0 0;">Código verificado. Ya podés cambiar tu contraseña.</p>
        </div>

        <form method="POST" action="{{ route('recuperar.profesor.guardar') }}">
            @csrf

            <div class="form-grupo">
                <label for="password">Contraseña nueva</label>
                <input type="password" name="password" id="password" minlength="6" maxlength="72" required autofocus>
                @error('password')
                    <div class="error-form">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-grupo">
                <label for="password_confirmation">Repetir contraseña nueva</label>
                <input type="password" name="password_confirmation" id="password_confirmation" minlength="6" maxlength="72" required>
            </div>

            <button type="submit" class="btn btn-bloque">Guardar contraseña</button>
        </form>
    </div>
</div>
@endsection
