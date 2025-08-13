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
        Schema::table('postulaciones', function (Blueprint $table) {
            $table->string('codigo_postulante', 20)->unique()->after('id')
                ->comment('Código único del postulante basado en correlativo del ciclo');
            $table->string('documento_constancia')->nullable()->after('estado')
                ->comment('Ruta del documento de constancia firmado y escaneado');
            $table->timestamp('fecha_constancia_generada')->nullable()
                ->comment('Fecha y hora cuando se generó la constancia');
            $table->timestamp('fecha_constancia_subida')->nullable()
                ->comment('Fecha y hora cuando se subió la constancia firmada');
            $table->boolean('constancia_generada')->default(false)
                ->comment('Indica si ya se generó la constancia PDF');
            $table->boolean('constancia_firmada')->default(false)
                ->comment('Indica si ya se subió la constancia firmada');
            
            // Índices
            $table->index('codigo_postulante');
            $table->index('constancia_generada');
            $table->index('constancia_firmada');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('postulaciones', function (Blueprint $table) {
            $table->dropIndex(['codigo_postulante']);
            $table->dropIndex(['constancia_generada']);
            $table->dropIndex(['constancia_firmada']);
            
            $table->dropColumn([
                'codigo_postulante',
                'documento_constancia',
                'fecha_constancia_generada',
                'fecha_constancia_subida',
                'constancia_generada',
                'constancia_firmada'
            ]);
        });
    }
};
