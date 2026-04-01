<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ciclos', function (Blueprint $table) {
            $table->unsignedBigInteger('programa_id')->nullable()->after('id');
            $table->foreign('programa_id')->references('id')->on('programas_academicos');
        });

        // Asignación automática inteligente basada en el nombre
        // ID 1 = CEPRE Regular
        // ID 2 = Reforzamiento Escolar
        
        DB::table('ciclos')
            ->where('nombre', 'like', '%Reforzamiento%')
            ->update(['programa_id' => 2]);

        DB::table('ciclos')
            ->whereNull('programa_id')
            ->update(['programa_id' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ciclos', function (Blueprint $table) {
            $table->dropForeign(['programa_id']);
            $table->dropColumn('programa_id');
        });
    }
};
