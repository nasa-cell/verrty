<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_sistema', 100)->default('ORDENIA IA');
            $table->integer('tiempo_limite_segundos')->default(30);
            $table->integer('cantidad_minima_numeros')->default(3);
            $table->integer('cantidad_maxima_numeros')->default(10);
            $table->integer('puntaje_base')->default(100);
            $table->boolean('activo')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion');
    }
};
