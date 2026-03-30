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
        Schema::table('inscripciones_reforzamiento', function (Blueprint $table) {
            $table->unsignedBigInteger('aula_id')->nullable()->after('turno');
            $table->string('nro_constancia')->nullable()->after('aula_id');
            
            $table->foreign('aula_id')->references('id')->on('aulas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscripciones_reforzamiento', function (Blueprint $table) {
            $table->dropForeign(['aula_id']);
            $table->dropColumn(['aula_id', 'nro_constancia']);
        });
    }
};
