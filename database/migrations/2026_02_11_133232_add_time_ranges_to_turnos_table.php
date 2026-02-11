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
        Schema::table('turnos', function (Blueprint $table) {
            // Campos para definir los rangos de asistencia con precisión de minutos
            $table->time('hora_entrada_inicio')->nullable()->after('hora_fin')->comment('Inicio del rango de entrada normal');
            $table->time('hora_entrada_fin')->nullable()->after('hora_entrada_inicio')->comment('Fin del rango de entrada normal');
            
            $table->time('hora_tarde_inicio')->nullable()->after('hora_entrada_fin')->comment('Inicio del rango considerado Tarde');
            $table->time('hora_tarde_fin')->nullable()->after('hora_tarde_inicio')->comment('Fin del rango considerado Tarde');
            
            $table->time('hora_salida_inicio')->nullable()->after('hora_tarde_fin')->comment('Inicio del rango de salida normal');
            $table->time('hora_salida_fin')->nullable()->after('hora_salida_inicio')->comment('Fin del rango de salida normal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('turnos', function (Blueprint $table) {
            $table->dropColumn([
                'hora_entrada_inicio', 
                'hora_entrada_fin', 
                'hora_tarde_inicio', 
                'hora_tarde_fin', 
                'hora_salida_inicio',
                'hora_salida_fin'
            ]);
        });
    }
};
