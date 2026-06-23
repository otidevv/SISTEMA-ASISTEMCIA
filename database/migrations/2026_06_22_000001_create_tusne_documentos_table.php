<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Documento(s) oficial(es) del TUSNE en PDF — sustento legal del catálogo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tusne_documentos', function (Blueprint $table) {
            $table->id();
            $table->string('anio')->nullable();          // ej. 2024
            $table->string('nombre_original')->nullable();
            $table->string('path');                      // disco public
            $table->boolean('vigente')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tusne_documentos');
    }
};
