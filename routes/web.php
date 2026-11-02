<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Alumno\DashboardController as AlumnoDashboardController;
use App\Http\Controllers\Alumno\JuegoController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RecuperacionController;
use App\Http\Controllers\Auth\RegistroController;
use App\Http\Controllers\Profesor\DashboardController as ProfesorDashboardController;
use App\Http\Controllers\Profesor\VerrityController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\VozController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

// ---------------------------------------------------------------------
// Autenticación
// ---------------------------------------------------------------------
Route::get('/login', [LoginController::class, 'mostrar'])->name('login');
Route::post('/login', [LoginController::class, 'iniciar'])->name('login.intentar');
Route::post('/logout', [LoginController::class, 'salir'])->middleware('auth')->name('logout');

// Autorregistro (solo alumnos; ver RegistroController).
Route::get('/registro', [RegistroController::class, 'mostrar'])->name('registro');
Route::post('/registro', [RegistroController::class, 'registrar'])->name('registro.crear');

// Recuperar contraseña de profesor por código fijo del sistema (no hay
// envío de correo real). Ver RecuperacionController.
Route::prefix('recuperar-profesor')->name('recuperar.profesor.')->group(function () {
    Route::get('/', [RecuperacionController::class, 'mostrarCodigo'])->name('mostrar');
    Route::post('/verificar', [RecuperacionController::class, 'verificarCodigo'])->name('verificar');
    Route::get('/nueva-contrasena', [RecuperacionController::class, 'mostrarNuevaContrasena'])->name('nueva');
    Route::post('/nueva-contrasena', [RecuperacionController::class, 'guardarNuevaContrasena'])->name('guardar');
});

// Voz de Verrity (Piper TTS local, con fallback a Web Speech API en el navegador).
Route::get('/voz', [VozController::class, 'sintetizar'])->middleware('auth')->name('voz.sintetizar');

// ---------------------------------------------------------------------
// Alumno
// ---------------------------------------------------------------------
Route::middleware(['auth', 'rol:alumno'])->prefix('alumno')->name('alumno.')->group(function () {
    Route::get('/dashboard', [AlumnoDashboardController::class, 'index'])->name('dashboard');
    Route::get('/jugar', [JuegoController::class, 'mostrar'])->name('jugar');
    Route::post('/comprobar', [JuegoController::class, 'comprobar'])->name('comprobar');
});

// ---------------------------------------------------------------------
// Profesor (Verrity IA completa)
// ---------------------------------------------------------------------
Route::middleware(['auth', 'rol:profesor'])->prefix('profesor')->name('profesor.')->group(function () {
    Route::get('/dashboard', [ProfesorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/alumnos/buscar', [ProfesorDashboardController::class, 'buscarAlumnos'])->name('alumnos.buscar');
    Route::post('/verrity/preguntar', [VerrityController::class, 'preguntar'])->name('verrity.preguntar');
    Route::post('/verrity/preguntar-alumno', [VerrityController::class, 'preguntarSobreAlumno'])->name('verrity.preguntar-alumno');
    Route::post('/verrity/generar', [VerrityController::class, 'generar'])->name('verrity.generar');
});

// ---------------------------------------------------------------------
// Reportes (profesor)
// ---------------------------------------------------------------------
Route::middleware(['auth', 'rol:profesor'])->prefix('reportes')->name('reportes.')->group(function () {
    Route::get('/', [ReporteController::class, 'index'])->name('index');
    Route::post('/general', [ReporteController::class, 'generarGeneral'])->name('general');
    Route::post('/individual', [ReporteController::class, 'generarIndividual'])->name('individual');
    Route::get('/{reporte}/imprimir', [ReporteController::class, 'imprimir'])->name('imprimir');
    Route::get('/{reporte}/pdf', [ReporteController::class, 'descargarPdf'])->name('pdf');
    Route::delete('/{reporte}', [ReporteController::class, 'eliminar'])->name('eliminar');
});

// ---------------------------------------------------------------------
// Administrador
// ---------------------------------------------------------------------
Route::middleware(['auth', 'rol:administrador'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::patch('/configuracion-ia', [AdminDashboardController::class, 'actualizarConfiguracionIa'])->name('configuracion-ia.actualizar');
    Route::patch('/usuarios/{usuario}/estado', [AdminDashboardController::class, 'cambiarEstadoUsuario'])->name('usuarios.estado');
});
