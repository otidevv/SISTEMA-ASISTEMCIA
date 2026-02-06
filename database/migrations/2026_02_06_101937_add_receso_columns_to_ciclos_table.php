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
        Schema::table('ciclos', function (Blueprint $table) {
            // Campos para horarios de receso configurables por ciclo
            // Nullable para ciclos que no tengan turno mañana o tarde
            $table->time('receso_manana_inicio')->nullable()->after('incluye_sabados');
            $table->time('receso_manana_fin')->nullable()->after('receso_manana_inicio');
            $table->time('receso_tarde_inicio')->nullable()->after('receso_manana_fin');
            $table->time('receso_tarde_fin')->nullable()->after('receso_tarde_inicio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ciclos', function (Blueprint $table) {
            $table->dropColumn([
                'receso_manana_inicio',
                'receso_manana_fin',
                'receso_tarde_inicio',
                'receso_tarde_fin'
            ]);
        });
    }
};
