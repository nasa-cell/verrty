<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partidas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('usuarios')->cascadeOnDelete();
            $table->integer('total_ejercicios')->default(0);
            $table->integer('aciertos')->default(0);
            $table->integer('errores')->default(0);
            $table->integer('puntaje_total')->default(0);
            $table->integer('tiempo_total_segundos')->default(0);
            $table->decimal('tiempo_promedio_segundos', 10, 2)->default(0);
            $table->decimal('porcentaje', 5, 2)->default(0);
            $table->enum('estado', ['iniciada', 'finalizada', 'finalizada_por_tiempo'])->default('iniciada');
            $table->dateTime('fecha_inicio')->useCurrent();
            $table->dateTime('fecha_fin')->nullable();

            $table->index('alumno_id', 'idx_partidas_alumno');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partidas');
    }
};
