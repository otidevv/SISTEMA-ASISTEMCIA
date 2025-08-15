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
        Schema::table('postulaciones', function (Blueprint $table) {
            // Agregar campos para documentos adicionales
            $table->string('carta_compromiso_path')->nullable()->after('constancia_firmada_path');
            $table->string('constancia_estudios_path')->nullable()->after('carta_compromiso_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('postulaciones', function (Blueprint $table) {
            $table->dropColumn(['carta_compromiso_path', 'constancia_estudios_path']);
        });
    }
};
