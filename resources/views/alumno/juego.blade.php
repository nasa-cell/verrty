@extends('layouts.app')

@section('content')
<div class="tarjeta juego-tablero" style="margin-top:20px;">
    <div class="juego-instruccion">ORDENA DE MAYOR A MENOR</div>

    <div class="temporizador" id="temporizador">{{ $segundosRestantes }}s</div>

    <div class="zona-fichas" id="zona-fichas"
         data-ejercicio-id="{{ $ejercicio->id }}"
         data-segundos-restantes="{{ $segundosRestantes }}">
        @foreach ($numeros as $numero)
            <div class="ficha-numero" draggable="true" data-valor="{{ $numero }}">{{ $numero }}</div>
        @endforeach
    </div>

    <button type="button" class="btn" id="btn-comprobar" data-url="{{ route('alumno.comprobar') }}">COMPROBAR</button>

    <div class="resultado-mensaje" id="resultado-mensaje"></div>

    <p style="color:var(--gris-500);font-size:.85rem;margin-top:18px;">
        Toca dos números para intercambiarlos, o arrástralos en escritorio.
    </p>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/juego.js') }}?v={{ filemtime(public_path('assets/js/juego.js')) }}"></script>
@endpush
