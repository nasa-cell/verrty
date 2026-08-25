<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $titulo ?? 'ORDENIA IA' }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/ordenia.css') }}?v={{ filemtime(public_path('assets/css/ordenia.css')) }}">
</head>
<body>
    <header class="barra-superior no-imprimir">
        <div class="contenedor">
            <a href="{{ url('/') }}" class="marca" style="text-decoration:none;color:inherit;">
                <span class="numero-marca">🔢</span> ORDENIA IA
            </a>

            @auth
                @php
                    $rutaPanel = match (auth()->user()->rol) {
                        'profesor' => 'profesor.dashboard',
                        'administrador' => 'admin.dashboard',
                        default => 'alumno.dashboard',
                    };
                @endphp

                <div class="usuario-actual">
                    @unless (request()->routeIs($rutaPanel))
                        <a href="{{ route($rutaPanel) }}" class="btn btn-secundario" style="padding:8px 14px;">← Volver al panel</a>
                    @endunless
                    <span class="badge-rol">{{ ucfirst(auth()->user()->rol) }}</span>
                    <span>{{ auth()->user()->nombre_completo }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-secundario" style="padding:8px 14px;">Salir</button>
                    </form>
                </div>
            @endauth
        </div>
    </header>

    <main class="contenedor">
        @if (session('mensaje'))
            <div class="tarjeta" style="margin-top:16px;border-left:4px solid var(--verde);">
                {{ session('mensaje') }}
            </div>
        @endif

        @yield('content')
    </main>

    <div class="pie-pagina no-imprimir">ORDENIA IA — Sistema educativo con IA propia (Verrity)</div>

    {{-- Modal de confirmación propio, usado por cualquier <form data-confirmar="..."> --}}
    <div class="modal-overlay no-imprimir" id="modal-confirmacion" hidden>
        <div class="modal-caja" role="alertdialog" aria-modal="true" aria-labelledby="modal-confirmacion-mensaje">
            <div class="modal-icono">⚠️</div>
            <p class="modal-mensaje" id="modal-confirmacion-mensaje"></p>
            <div class="modal-acciones">
                <button type="button" class="btn btn-secundario" id="modal-confirmacion-cancelar">Cancelar</button>
                <button type="button" class="btn btn-peligro" id="modal-confirmacion-aceptar">Eliminar</button>
            </div>
        </div>
    </div>
    <script src="{{ asset('assets/js/confirmar.js') }}?v={{ filemtime(public_path('assets/js/confirmar.js')) }}"></script>

    @stack('scripts')
</body>
</html>
