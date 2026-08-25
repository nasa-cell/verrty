<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ejercicios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('usuarios')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('profesor_id')->nullable()->constrained('usuarios')->nullOnDelete()->cascadeOnUpdate();
            $table->text('numeros_generados');
            $table->text('respuesta_correcta');
            $table->integer('cantidad_numeros');
            $table->integer('dificultad')->default(1);
            $table->enum('tipo_numeros', ['positivos', 'negativos', 'mixtos', 'decimales'])->default('positivos');
            $table->boolean('generado_por_ia')->default(true);
            $table->enum('estado', ['pendiente', 'respondido', 'vencido'])->default('pendiente');
            $table->integer('tiempo_limite_segundos')->default(30);
            $table->dateTime('fecha_generacion')->useCurrent();
            $table->dateTime('fecha_inicio')->nullable();
            $table->dateTime('fecha_fin')->nullable();

            $table->index('alumno_id', 'idx_ejercicios_alumno');
            $table->index('fecha_generacion', 'idx_ejercicios_fecha');
        });

        DB::statement('ALTER TABLE ejercicios ADD CONSTRAINT chk_ejercicio_dificultad CHECK (dificultad BETWEEN 1 AND 10)');
        DB::statement('ALTER TABLE ejercicios ADD CONSTRAINT chk_cantidad_numeros CHECK (cantidad_numeros BETWEEN 3 AND 10)');
    }

    public function down(): void
    {
        Schema::dropIfExists('ejercicios');
    }
};
