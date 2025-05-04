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
        Schema::create('registros_asistencia', function (Blueprint $table) {
            $table->id(); // Esto crea un bigIncrements (unsignedBigInteger)
            $table->string('nro_documento');
            $table->dateTime('fecha_hora');
            $table->string('tipo_verificacion');
            $table->string('estado');
            $table->string('codigo_trabajo')->nullable();
            $table->unsignedBigInteger('terminal_id')->nullable();
            $table->string('sn_dispositivo')->nullable();
            $table->timestamp('fecha_registro')->useCurrent();
            // Sin timestamps() para mantener compatibilidad con el otro sistema
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registros_asistencia');
    }
};
