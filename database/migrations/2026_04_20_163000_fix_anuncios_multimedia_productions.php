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
        // 1. Asegurar columnas multimedia en la tabla anuncios
        if (Schema::hasTable('anuncios')) {
            Schema::table('anuncios', function (Blueprint $table) {
                if (!Schema::hasColumn('anuncios', 'fecha_publicacion')) {
                    $table->datetime('fecha_publicacion')->nullable()->after('es_activo');
                }
                if (!Schema::hasColumn('anuncios', 'fecha_expiracion')) {
                    $table->datetime('fecha_expiracion')->nullable()->after('fecha_publicacion');
                }
                if (!Schema::hasColumn('anuncios', 'imagen')) {
                    $table->string('imagen')->nullable()->after('tipo');
                }
                if (!Schema::hasColumn('anuncios', 'archivo_adjunto')) {
                    $table->string('archivo_adjunto')->nullable()->after('imagen');
                }
                if (!Schema::hasColumn('anuncios', 'tipo_archivo')) {
                    $table->string('tipo_archivo', 50)->nullable()->after('archivo_adjunto');
                }
                if (!Schema::hasColumn('anuncios', 'enviar_push')) {
                    $table->boolean('enviar_push')->default(false)->after('tipo_archivo');
                }
                if (!Schema::hasColumn('anuncios', 'firebase_message_id')) {
                    $table->string('firebase_message_id')->nullable()->after('enviar_push');
                }
                
                // Eliminar el índice antiguo si existe (preventivo)
                // $table->dropIndex(['dirigido_a']); // Solo si estás seguro que existe
            });
        }

        // 2. Crear la tabla pivote de roles si no existe
        if (!Schema::hasTable('anuncio_roles')) {
            Schema::create('anuncio_roles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('anuncio_id')->constrained('anuncios')->onDelete('cascade');
                $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anuncio_roles');
        
        if (Schema::hasTable('anuncios')) {
            Schema::table('anuncios', function (Blueprint $table) {
                $table->dropColumn([
                    'fecha_publicacion', 
                    'fecha_expiracion', 
                    'imagen', 
                    'archivo_adjunto', 
                    'tipo_archivo', 
                    'enviar_push', 
                    'firebase_message_id'
                ]);
            });
        }
    }
};
