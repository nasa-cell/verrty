<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generaciones_ia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profesor_id')->constrained('usuarios')->cascadeOnDelete();
            $table->foreignId('alumno_id')->constrained('usuarios')->cascadeOnDelete();
            $table->decimal('porcentaje_anterior', 5, 2);
            $table->decimal('tiempo_promedio_anterior', 10, 2);
            $table->integer('dificultad_anterior');
            $table->integer('dificultad_nueva');
            $table->integer('cantidad_numeros');
            $table->text('numeros_generados');
            $table->enum('decision_ia', ['subir_dificultad', 'mantener_dificultad', 'bajar_dificultad']);
            $table->text('motivo_decision');
            $table->string('frase_verrity', 500)->nullable();
            $table->dateTime('fecha_generacion')->useCurrent();

            $table->index('alumno_id', 'idx_generaciones_alumno');
            $table->index('fecha_generacion', 'idx_generaciones_fecha');
        });

        DB::statement('ALTER TABLE generaciones_ia ADD CONSTRAINT chk_dificultad_anterior CHECK (dificultad_anterior BETWEEN 1 AND 10)');
        DB::statement('ALTER TABLE generaciones_ia ADD CONSTRAINT chk_dificultad_nueva CHECK (dificultad_nueva BETWEEN 1 AND 10)');
    }

    public function down(): void
    {
        Schema::dropIfExists('generaciones_ia');
    }
};
