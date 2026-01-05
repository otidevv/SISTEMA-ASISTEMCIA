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
        Schema::table('carnets', function (Blueprint $table) {
            $table->boolean('entregado')->default(false)->after('impreso');
            $table->datetime('fecha_entrega')->nullable()->after('entregado');
            $table->unsignedBigInteger('entregado_por')->nullable()->after('fecha_entrega');
            $table->string('ip_entrega', 45)->nullable()->after('entregado_por');
            
            // Foreign key para el usuario que entregó
            $table->foreign('entregado_por')->references('id')->on('users')->onDelete('set null');
            
            // Índices para mejorar consultas
            $table->index('entregado');
            $table->index(['entregado', 'fecha_entrega']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carnets', function (Blueprint $table) {
            $table->dropForeign(['entregado_por']);
            $table->dropIndex(['carnets_entregado_index']);
            $table->dropIndex(['carnets_entregado_fecha_entrega_index']);
            $table->dropColumn(['entregado', 'fecha_entrega', 'entregado_por', 'ip_entrega']);
        });
    }
};
