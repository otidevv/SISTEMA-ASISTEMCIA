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
        Schema::create('user_biometrics', function (Blueprint $table) {
            $table->id();
            $table->string('numero_documento', 20); // Referencia al DNI del usuario
            $table->enum('type', ['fingerprint', 'face']);
            $table->integer('biometric_index'); // FID (0-9 para huella, 0-X para rostro)
            $table->string('sn_dispositivo', 50)->nullable();
            $table->timestamps();

            $table->unique(['numero_documento', 'type', 'biometric_index'], 'idx_user_biometric_unique');
            $table->index('numero_documento');
        });

        // Opcionalmente, agregar columnas de caché en users para rapidez
        Schema::table('users', function (Blueprint $table) {
            $table->integer('fingerprint_count')->default(0)->after('has_fingerprint');
            $table->integer('face_count')->default(0)->after('has_face');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_biometrics');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['fingerprint_count', 'face_count']);
        });
    }
};
