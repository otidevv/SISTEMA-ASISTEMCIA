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
            $table->unsignedBigInteger('actualizado_por')->nullable()->after('observaciones');
            $table->timestamp('fecha_actualizacion')->nullable()->after('actualizado_por');
            
            // Agregar índice de clave foránea
            $table->foreign('actualizado_por')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('postulaciones', function (Blueprint $table) {
            $table->dropForeign(['actualizado_por']);
            $table->dropColumn(['actualizado_por', 'fecha_actualizacion']);
        });
    }
};
