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
        Schema::table('carnet_templates', function (Blueprint $table) {
            $table->foreignId('ciclo_id')->nullable()->constrained('ciclos')->after('nombre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carnet_templates', function (Blueprint $table) {
            $table->dropForeign(['ciclo_id']);
            $table->dropColumn('ciclo_id');
        });
    }
};
