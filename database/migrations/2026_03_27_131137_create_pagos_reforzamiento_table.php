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
        Schema::create('pagos_reforzamiento', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('inscripcion_id')->unsigned();
            $table->string('numero_operacion', 50)->unique();
            $table->decimal('monto', 10, 2)->default(200.00);
            $table->date('fecha_pago');
            $table->string('mes_pagado', 20);
            $table->string('voucher_path', 191)->nullable();
            $table->tinyInteger('verificado_api')->default(0);
            $table->timestamp('fecha_verificacion_api')->nullable();
            $table->enum('estado_pago', ['pendiente', 'aprobado', 'rechazado'])->default('pendiente');
            $table->bigInteger('validado_por')->unsigned()->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('inscripcion_id')->references('id')->on('inscripciones_reforzamiento')->onDelete('cascade');
            $table->foreign('validado_por')->references('id')->on('users');

            // Indexes
            $table->index('mes_pagado');
            $table->index('estado_pago');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos_reforzamiento');
    }
};
