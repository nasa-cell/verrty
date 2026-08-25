<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progreso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->unique()->constrained('usuarios')->cascadeOnDelete();
            $table->integer('ejercicios_completados')->default(0);
            $table->integer('aciertos')->default(0);
            $table->integer('errores')->default(0);
            $table->decimal('porcentaje_progreso', 5, 2)->default(0);
            $table->integer('dificultad_actual')->default(1);
            $table->integer('puntaje_total')->default(0);
            $table->dateTime('ultima_actividad')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progreso');
    }
};
