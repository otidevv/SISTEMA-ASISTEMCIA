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
            // Agregar campos faltantes si no existen
            if (!Schema::hasColumn('postulaciones', 'foto_path')) {
                $table->string('foto_path')->nullable()->after('certificado_estudios_path');
            }
            if (!Schema::hasColumn('postulaciones', 'voucher_path')) {
                $table->string('voucher_path')->nullable()->after('foto_path');
            }
            if (!Schema::hasColumn('postulaciones', 'constancia_firmada_path')) {
                $table->string('constancia_firmada_path')->nullable()->after('voucher_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('postulaciones', function (Blueprint $table) {
            $table->dropColumn(['foto_path', 'voucher_path', 'constancia_firmada_path']);
        });
    }
};
