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
        Schema::table('pagos_docentes', function (Blueprint $table) {
            $table->unsignedBigInteger('ciclo_id')->nullable()->after('docente_id');
            $table->foreign('ciclo_id')->references('id')->on('ciclos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pagos_docentes', function (Blueprint $table) {
            $table->dropForeign(['ciclo_id']);
            $table->dropColumn('ciclo_id');
        });
    }
};
