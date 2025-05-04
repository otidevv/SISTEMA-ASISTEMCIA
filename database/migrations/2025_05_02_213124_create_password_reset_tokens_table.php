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
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->id(); // Cambiado de uuid a id() (auto-incremental)
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->string('token', 100)->unique();
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamp('fecha_expiracion');
            $table->boolean('utilizado')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
    }
};
