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
            $table->boolean('incluye_sabados')->default(false)->after('es_activo')
                ->comment('Indica si el ciclo tiene clases los sábados (true) o solo de lunes a viernes (false)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ciclos', function (Blueprint $table) {
            $table->dropColumn('incluye_sabados');
        });
    }
};
