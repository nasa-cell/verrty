<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rendimiento_alumnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->unique()
                ->constrained('usuarios')->cascadeOnDelete()->cascadeOnUpdate();
            $table->integer('total_ejercicios')->default(0);
            $table->integer('total_aciertos')->default(0);
            $table->integer('total_errores')->default(0);
            $table->decimal('porcentaje_aciertos', 5, 2)->default(0);
            $table->decimal('porcentaje_errores', 5, 2)->default(0);
            $table->decimal('tiempo_promedio_segundos', 10, 2)->default(0);
            $table->integer('dificultad_actual')->default(1);
            $table->integer('racha_actual')->default(0);
            $table->integer('mejor_racha')->default(0);
            $table->integer('puntaje_total')->default(0);
            $table->dateTime('ultima_evaluacion')->nullable();
            $table->timestamps();
        });

        DB::statement('ALTER TABLE rendimiento_alumnos ADD CONSTRAINT chk_dificultad CHECK (dificultad_actual BETWEEN 1 AND 10)');
    }

    public function down(): void
    {
        Schema::dropIfExists('rendimiento_alumnos');
    }
};
