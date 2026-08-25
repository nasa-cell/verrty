<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('respuestas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ejercicio_id')->constrained('ejercicios')->cascadeOnDelete();
            $table->foreignId('alumno_id')->constrained('usuarios')->cascadeOnDelete();
            $table->text('respuesta_usuario');
            $table->boolean('es_correcta')->default(false);
            $table->integer('tiempo_segundos')->default(0);
            $table->integer('puntaje_obtenido')->default(0);
            $table->boolean('respondido_por_tiempo')->default(false);
            $table->dateTime('fecha_respuesta')->useCurrent();

            $table->index('alumno_id', 'idx_respuestas_alumno');
            $table->index('fecha_respuesta', 'idx_respuestas_fecha');
        });

        DB::statement('ALTER TABLE respuestas ADD CONSTRAINT chk_tiempo_respuesta CHECK (tiempo_segundos >= 0)');
        DB::statement('ALTER TABLE respuestas ADD CONSTRAINT chk_puntaje CHECK (puntaje_obtenido >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('respuestas');
    }
};
