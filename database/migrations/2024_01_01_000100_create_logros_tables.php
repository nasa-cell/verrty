<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logros', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->string('descripcion', 255);
            $table->integer('requisito')->default(0);
            $table->string('icono', 100)->nullable();
            $table->boolean('activo')->default(true);
        });

        Schema::create('usuario_logros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('usuarios')->cascadeOnDelete();
            $table->foreignId('logro_id')->constrained('logros')->cascadeOnDelete();
            $table->dateTime('fecha_obtenido')->useCurrent();

            $table->unique(['alumno_id', 'logro_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_logros');
        Schema::dropIfExists('logros');
    }
};
