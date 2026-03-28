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
        // Modificar ENUM para incluir reforzamiento_colegio
        DB::statement("ALTER TABLE carnet_templates MODIFY COLUMN tipo ENUM('postulante', 'estudiante', 'docente', 'administrativo', 'reforzamiento_colegio') DEFAULT 'postulante'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir ENUM
        DB::statement("ALTER TABLE carnet_templates MODIFY COLUMN tipo ENUM('postulante', 'estudiante', 'docente', 'administrativo') DEFAULT 'postulante'");
    }
};
