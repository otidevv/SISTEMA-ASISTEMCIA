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
        Schema::create('apoderados_reforzamiento', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('inscripcion_id')->unsigned();
            $table->string('numero_documento', 12);
            $table->string('nombres', 100);
            $table->string('celular', 20);
            $table->string('email', 100)->nullable();
            $table->enum('parentesco', ['Padre', 'Madre', 'Tutor']);
            $table->timestamps();

            // Foreign key
            $table->foreign('inscripcion_id')->references('id')->on('inscripciones_reforzamiento')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apoderados_reforzamiento');
    }
};
