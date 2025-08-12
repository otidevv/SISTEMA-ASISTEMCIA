<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('centros_educativos', function (Blueprint $table) {
            $table->id();
            $table->string('d_dpto', 100)->index(); // Departamento
            $table->string('d_prov', 100)->index(); // Provincia
            $table->string('d_dist', 100)->index(); // Distrito
            $table->string('cen_edu', 255); // Nombre del centro educativo
            $table->string('d_niv_mod', 100)->nullable(); // Nivel/Modalidad
            $table->string('dir_cen', 255)->nullable(); // Dirección del centro
            $table->boolean('estado')->default(true);
            $table->timestamps();
            
            // Índice compuesto para búsquedas rápidas
            $table->index(['d_dpto', 'd_prov', 'd_dist']);
            $table->index('cen_edu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('centros_educativos');
    }
};
