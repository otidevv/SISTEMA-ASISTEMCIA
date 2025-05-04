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
        Schema::create('parentescos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('padre_id')->constrained('users')->onDelete('cascade');
            $table->string('tipo_parentesco', 30); // 'padre', 'madre', 'tutor', etc.
            $table->boolean('acceso_portal')->default(true);
            $table->boolean('recibe_notificaciones')->default(true);
            $table->boolean('contacto_emergencia')->default(false);
            $table->boolean('estado')->default(true);
            $table->timestamps();

            // Evitar duplicados del mismo tipo de parentesco
            $table->unique(['estudiante_id', 'padre_id', 'tipo_parentesco']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parentescos');
    }
};
