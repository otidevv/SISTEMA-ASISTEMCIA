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
        Schema::table('role_permissions', function (Blueprint $table) {
            // Agregar timestamps si no existen
            if (!Schema::hasColumn('role_permissions', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            
            if (!Schema::hasColumn('role_permissions', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        // Actualizar registros existentes con la fecha actual
        DB::table('role_permissions')
            ->whereNull('created_at')
            ->update([
                'created_at' => now(),
                'updated_at' => now()
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('role_permissions', function (Blueprint $table) {
            $table->dropColumn(['created_at', 'updated_at']);
        });
    }
};