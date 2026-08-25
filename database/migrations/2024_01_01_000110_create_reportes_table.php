<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profesor_id')->constrained('usuarios')->cascadeOnDelete();
            $table->foreignId('alumno_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('tipo', 100);
            $table->string('titulo', 200);
            $table->longText('contenido');
            $table->dateTime('fecha_generacion')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes');
    }
};
