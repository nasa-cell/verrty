<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Parámetros del árbol de decisiones de Verrity (AIConfigurationManager).
 * Editable únicamente por el administrador.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_ia', function (Blueprint $table) {
            $table->id();
            $table->decimal('porcentaje_subir', 5, 2)->default(85.00);
            $table->decimal('porcentaje_mantener', 5, 2)->default(60.00);
            $table->decimal('porcentaje_bajar', 5, 2)->default(59.99);
            $table->integer('dificultad_minima')->default(1);
            $table->integer('dificultad_maxima')->default(10);
            $table->integer('numeros_minimos')->default(3);
            $table->integer('numeros_maximos')->default(10);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_ia');
    }
};
