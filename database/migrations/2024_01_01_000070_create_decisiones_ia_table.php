<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Bitácora del árbol de decisiones (DecisionTreeService). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('decisiones_ia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('usuarios')->cascadeOnDelete();
            $table->decimal('porcentaje_aciertos', 5, 2);
            $table->decimal('tiempo_promedio', 10, 2);
            $table->integer('errores')->default(0);
            $table->integer('dificultad_anterior');
            $table->string('decision', 100);
            $table->integer('dificultad_resultante');
            $table->text('explicacion');
            $table->dateTime('fecha_decision')->useCurrent();

            $table->index('alumno_id', 'idx_decisiones_alumno');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('decisiones_ia');
    }
};
