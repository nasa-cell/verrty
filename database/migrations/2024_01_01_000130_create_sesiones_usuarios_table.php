<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bitácora de inicios de sesión de la aplicación (distinta de la tabla
 * "sessions" que usa el driver de sesión de Laravel). Se usa para
 * auditoría de accesos por usuario.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sesiones_usuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->string('token')->unique();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->dateTime('inicio')->useCurrent();
            $table->dateTime('ultimo_acceso')->useCurrent();
            $table->dateTime('cierre')->nullable();
            $table->boolean('activa')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sesiones_usuarios');
    }
};
