<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('postulaciones', function (Blueprint $table) {
            // Cambiar de ENUM a VARCHAR para soportar más valores
            DB::statement("ALTER TABLE postulaciones MODIFY COLUMN tipo_inscripcion VARCHAR(50) NOT NULL DEFAULT 'Regular'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('postulaciones', function (Blueprint $table) {
            // Revertir a ENUM original
            DB::statement("ALTER TABLE postulaciones MODIFY COLUMN tipo_inscripcion ENUM('postulante', 'reforzamiento') NOT NULL");
        });
    }
};
