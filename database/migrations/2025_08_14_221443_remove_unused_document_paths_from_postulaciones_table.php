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
            // Eliminar campos que ya no se usan (si existen)
            $columnsToRemove = [
                'voucher_pago_path',
                'foto_carnet_path', 
                'carta_compromiso_path',
                'constancia_estudios_path'
            ];
            
            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('postulaciones', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('postulaciones', function (Blueprint $table) {
            // Restaurar los campos si se hace rollback
            $table->string('voucher_pago_path')->nullable();
            $table->string('foto_carnet_path')->nullable();
            $table->string('carta_compromiso_path')->nullable();
            $table->string('constancia_estudios_path')->nullable();
        });
    }
};
