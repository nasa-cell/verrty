<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ORDENIA IA
 *
 * Tabla base de autenticación y roles. Sustituye a la tabla "users" por
 * defecto de Laravel por "usuarios", tal como exige el diseño del sistema
 * (PEA #6 Seguridad: autenticación, roles, autorización).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('email', 150)->unique();
            $table->string('password');
            $table->enum('rol', ['alumno', 'profesor', 'administrador'])->default('alumno');
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->dateTime('ultimo_acceso')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index('rol', 'idx_usuarios_rol');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('usuarios');
    }
};
