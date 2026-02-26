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
        Schema::create('biometric_commands', function (Blueprint $table) {
            $table->id();
            $table->string('device_sn');
            $table->string('command'); // ENROLL_FP, ENROLL_FACE, etc
            $table->text('payload')->nullable(); // UserID, Name, etc
            $table->enum('status', ['pending', 'sent', 'completed', 'error'])->default('pending');
            $table->text('response_data')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();

            $table->foreign('device_sn')->references('sn')->on('biometric_devices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biometric_commands');
    }
};
