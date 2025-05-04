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
        Schema::create('asistencia_eventos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('registros_asistencia_id');
            $table->boolean('procesado')->default(false);
            $table->timestamps();

            $table->foreign('registros_asistencia_id')
                ->references('id')
                ->on('registros_asistencia')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asistencia_eventos');
    }
};
