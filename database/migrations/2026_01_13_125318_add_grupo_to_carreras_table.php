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
        Schema::table('carreras', function (Blueprint $table) {
            $table->enum('grupo', ['A', 'B', 'C'])
                  ->nullable()
                  ->after('nombre')
                  ->comment('Grupo de carrera: A=Ingenierías, B=Ciencias de la Salud, C=Ciencias Sociales y Educación');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carreras', function (Blueprint $table) {
            $table->dropColumn('grupo');
        });
    }
};
