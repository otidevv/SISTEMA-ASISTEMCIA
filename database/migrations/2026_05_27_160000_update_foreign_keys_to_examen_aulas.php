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
        Schema::disableForeignKeyConstraints();

        // 1. Actualizar llave foránea en examen_estudiante_distribucion
        Schema::table('examen_estudiante_distribucion', function (Blueprint $table) {
            try {
                $table->dropForeign('examen_estudiante_distribucion_aula_id_foreign');
            } catch (\Exception $e) {}
            
            $table->foreign('aula_id')
                ->references('id')
                ->on('examen_aulas')
                ->onDelete('cascade');
        });

        // 2. Actualizar llave foránea en examen_distribucion
        Schema::table('examen_distribucion', function (Blueprint $table) {
            try {
                $table->dropForeign('examen_distribucion_aula_id_foreign');
            } catch (\Exception $e) {}

            $table->foreign('aula_id')
                ->references('id')
                ->on('examen_aulas')
                ->onDelete('cascade');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('examen_estudiante_distribucion', function (Blueprint $table) {
            try {
                $table->dropForeign(['aula_id']);
            } catch (\Exception $e) {}

            $table->foreign('aula_id')
                ->references('id')
                ->on('aulas')
                ->onDelete('cascade');
        });

        Schema::table('examen_distribucion', function (Blueprint $table) {
            try {
                $table->dropForeign(['aula_id']);
            } catch (\Exception $e) {}

            $table->foreign('aula_id')
                ->references('id')
                ->on('aulas')
                ->onDelete('cascade');
        });

        Schema::enableForeignKeyConstraints();
    }
};
