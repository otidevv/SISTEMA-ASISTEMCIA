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
        Schema::table('ciclo_carrera_vacantes', function (Blueprint $table) {
            $table->dropColumn('precio_inscripcion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ciclo_carrera_vacantes', function (Blueprint $table) {
            $table->decimal('precio_inscripcion', 10, 2)->nullable()->after('vacantes_reservadas');
        });
    }
};
